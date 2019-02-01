<?php
declare(strict_types=1);

namespace App\Service\Data;

use App\Entity\Edition;
use App\Service\Data\FrontFinder;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class WorkGroomer
{

    private $logger;
    private $em;
    private $frontFinder;

    private $asins                  = [];
    private $sigs                   = [];

    private $searched_asins         = [];
    private $searched_sigs          = [];
    private $searched_aws_asins     = [];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        FrontFinder $frontFinder
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->frontFinder          = $frontFinder;
    }

    public function workGroom(Edition $edition): void
    {
        $this->workGroomLogic($edition->getAsin());
    }

    public function workGroomLogic(string $asin): void
    {
        $em     = $this->em;
        $dbh    = $em->getConnection();
        $ff     = $this->frontFinder;
        $now    = new \DateTime('now');

        $sql = '
            SELECT
                asin, sig
            FROM
                edition
            WHERE
                asin = ?';

        $sth    = $dbh->prepare($sql);
        $sth->execute([$asin]);
        $r      = $sth->fetch();

        $this->asins    = [$r['asin'] => 1];
        $this->sigs     = [$r['sig'] => 1];

        $this->searched_asins       = [];
        $this->searched_sigs        = [];
        $this->searched_aws_asins   = [];

        // -------------------------------------------------------------------------
        // repeat while any of these is returning more records

        while ( max(
            $this->findNewSigAsins(),
            $this->findNewAsinSigs(),
            $this->findAWSRelated()) ){
        }

        // -------------------------------------------------------------------------
        // collect the edition data

        $in = '';
        foreach (array_keys($this->asins) as $asin){
            if ($in){
                $in .= ',';
            }
            $in .= '?';
        }

        $sql = "
            SELECT
                asin, work_id, title, format_id,
                amzn_large_cover, active, release_date
            FROM
                edition
            WHERE
                edition.amzn_updated_at IS NOT NULL AND
                asin IN ($in)";

        $sth = $dbh->prepare($sql);
        $sth->execute(array_keys($this->asins));

        $work_ids       = [];
        $edition_data   = [];

        foreach ($sth->fetchAll() as $r){

            // if the publisher forgot to inlude the release date
            // we still want to include the book but we will not
            // put it frontmost
            $releaseDate = $r['release_date'] ?? '1900-01-01 00:00:00';

            $edition_data[] = [
                'asin'          => $r['asin'],
                'work_id'       => $r['work_id'],
                'title'         => $r['title'],
                'format_id'     => $r['format_id'],
                'has_cover'     => (boolean)$r['amzn_large_cover'],
                'active'        => $r['active'],
                'releaseDate'   => new \Datetime($releaseDate)
            ];
            if ($r['work_id']){
                $work_ids[$r['work_id']] = 1;
            }
        }

        // -------------------------------------------------------------------------
        // find the front most edition

        $front = $ff->getFrontLogic($edition_data);

        if (!$front){
            // no viable front title

            foreach ($work_ids as $work_id){
                $sql = '
                    UPDATE
                        work
                    SET
                        updated_at = ?,
                        front_edition_id = NULL,
                        deleted = 1
                    WHERE
                        work.id = ?';

                $sth = $dbh->prepare($sql);
                $sth->execute(array(
                    $now->format('Y-m-d H:i:s'),
                    $work_id));
            }
            return;
        }

        // -------------------------------------------------------------------------
        // create, continue, or combine work records

        // work not found, create one
        if (0 === count(array_keys($work_ids))){

            $sql = '
                INSERT INTO work
                    (front_edition_asin, title, created_at, updated_at)
                VALUES
                    (?,?,?,?)
                ';

            $sth = $dbh->prepare($sql);
            $sth->execute(array(
                $front['asin'],
                $front['title'],
                $now->format('Y-m-d H:i:s'),
                $now->format('Y-m-d H:i:s')));

            $master_work_id = $dbh->lastInsertId();
        }
        // just one
        else if (1 === count(array_keys($work_ids))){
            $keys = array_keys($work_ids);
            $master_work_id = $keys[0];

            // update title, updated_at, front

            $sql = '
                UPDATE
                    work
                SET
                    title=?, updated_at=?, front_edition_asin=?
                WHERE
                    work.id = ?';

            $sth = $dbh->prepare($sql);
            $sth->execute(array(
                $front['title'],
                $now->format('Y-m-d H:i:s'),
                $front['asin'],
                $master_work_id));
        }
        // multilple works found, must be fixed
        else if (1 < count(array_keys($work_ids))){
            $in = '';
            foreach (array_keys($work_ids) as $work_id){
                if ($in){
                    $in .= ',';
                }
                $in .= '?';
            }

            $sql = "
                SELECT
                    id
                FROM
                    work
                WHERE
                    id in ($in)
                ORDER BY
                    created_at ASC
                LIMIT 1";

            $sth = $dbh->prepare($sql);
            $sth->execute(array_keys($work_ids));
            $r = $sth->fetch();
            $master_work_id = $r['id'];

            // - mark old ones as superseeded ---------------------------------------
            $in = '';
            $bad_work_ids = array();
            foreach (array_keys($work_ids) as $work_id){
                if ($work_id == $master_work_id){
                    continue;
                }
                if ($in){
                    $in .= ',';
                }
                $in .= '?';
                $bad_work_ids[$work_id] = 1;
            }

            $sql = "
                UPDATE work
                SET superseding_id=?, updated_at=?
                WHERE id IN ($in)
            ";
            $sth = $dbh->prepare($sql);
            $sth->execute( array_merge(
                [$master_work_id],
                [$now->format('Y-m-d H:i:s')],
                array_keys($bad_work_ids)) );
        }

        // -------------------------------------------------------------------------
        // update editions with new work ids

        $in         = '';
        $ids        = [];
        foreach ($edition_data as $ed){
            if ($in){
                $in .= ',';
            }
            $in .= '?';
            $asins[] = $ed['asin'];
        }
        $sql = "
            UPDATE
                edition
            SET
                work_id = ?, updated_at = ?
            WHERE
                asin IN ($in)
        ";

        $sth = $dbh->prepare($sql);
        $sth->execute(array_merge(
            [$master_work_id],
            [$now->format('Y-m-d H:i:s')],
            $asins
        ));

        // -------------------------------------------------------------------------
        // undelete works which have gained editions

        $sql = '
            UPDATE
                work
            SET
                updated_at = ?,
                deleted = 0
            WHERE
                work.id = ?';

        $sth = $dbh->prepare($sql);
        $sth->execute(array(
            $now->format('Y-m-d H:i:s'),
            $master_work_id));

        // -------------------------------------------------------------------------
        // find similar editions to this work's editions
        // re-use $in and $asins

        $sql = "
            SELECT
                similar_asin
            FROM
                similar_edition, edition
            WHERE
                edition_asin in ($in) AND
                edition.active = 1 AND
                edition.deleted = 0 AND
                edition.rejected = 0 AND
                edition.asin = similar_edition.similar_asin";

        $sth = $dbh->prepare($sql);
        $sth->execute($asins);

        $in     = '';
        $asins  = [];
        foreach ($sth->fetchAll() as $r){
            if ($in){
                $in .= ',';
            }
            $in .= '?';
            $asins[] = $r['similar_asin'];
        }

        $similar_work_ids = [];
        if (count($asins)){
            $sql = "
                SELECT
                    work_id
                FROM
                    edition, work
                WHERE
                    work.id = edition.work_id AND
                    work.deleted = 0 AND
                    edition.asin IN ($in)";

            $sth = $dbh->prepare($sql);
            $sth->execute($asins);

            $similar_work_ids = [];
            foreach ($sth->fetchAll() as $r){
                if ($r['work_id']){
                    $similar_work_ids[] = $r['work_id'];
                }
            }
        }

        $sql = '
            DELETE FROM similar_work
            WHERE work_id = ?';

        $sth = $dbh->prepare($sql);
        $sth->execute(array($master_work_id));

        if (count($similar_work_ids)){
            $sql = '
                INSERT INTO similar_work (work_id, similar_id)
                VALUES (?,?)';

            $sth = $dbh->prepare($sql);
            foreach ($similar_work_ids as $similar_work_id){
                $sth->execute(array($master_work_id, $similar_work_id));
            }
        }
    }

    private function findNewSigAsins(): bool
    {
        // returns true if any new asins are found
        // backstops against $this->searched_sigs

        $sql = '
            SELECT
                asin
            FROM
                edition
            WHERE
                asin IS NOT NULL AND
                sig IN (###IN###)';

        $new = false;
        $asins = $this->memoizedQueryByString(
            $this->sigs,
            $this->searched_sigs,
            $sql,
            'asin'
        );

        foreach ($asins as $asin){
            if (!isset($this->asins[(string)$asin])){
                $this->asins[(string)$asin] = 1;
                $new = true;
            }
        }
        return $new;
    }

    private function findNewAsinSigs(): bool
    {
        // returns true if any new sigs are found
        // backstops against $this->searched_asins

        $sql = '
            SELECT
                DISTINCT sig
            FROM
                edition
            WHERE
                sig IS NOT NULL AND
                asin IN (###IN###)';

        $new = false;
        $sigs =  $this->memoizedQueryByString(
            $this->asins,
            $this->searched_asins,
            $sql,
            'sig'
        );

        foreach ($sigs as $sig){
            if (!isset($this->sigs[$sig])){
                $this->sigs[$sig] = 1;
                $new = true;
            }
        }
        return $new;
    }

    private function findAWSRelated(): bool
    {
        // returns true if any new asins are found
        // backstops against $this->searched_aws_asins

        $dbh    = $this->em->getConnection();

        $sql = '
            SELECT asin, amzn_alternatives
            FROM edition
            WHERE asin in (#ASINS#)
        ';

        $in = '';
        $bound = [];

        foreach ($this->asins as $asin => $val){
            if (isset($this->searched_aws_asins[$asin])){
                continue;
            }
            $this->searched_aws_asins[(string)$asin] = 1;

            $in .= '?';
            $bound[] = $asin;
        }
        if (0 == count($bound)){
            return false;
        }

        $in = str_replace('??', '?,?', $in);
        $in = str_replace('??', '?,?', $in);

        $sql = str_replace('#ASINS#', $in, $sql);
        $sth = $dbh->prepare($sql);

        $i = 1;
        foreach ($bound as $b){
            $sth->bindValue($i, $b);
            $i++;
        }

        $sth->execute();

        $new = false;
        foreach ($sth->fetchAll() as $result){
            if ($result['amzn_alternatives']){
                foreach (explode(',', $result['amzn_alternatives']) as $fa){
                    if (!isset($this->asins[(string)$fa])){
                        $this->asins[(string)$fa] = 1;
                        $new = true;
                    }
                }
            }
        }
        return $new;
    }

    private function memoizedQueryByString(
        array $keys,
        array &$searched,
        string $sql,
        string $name
    ): array
    {
        $dbh    = $this->em->getConnection();
        $sp     = '';

        $search_keys = [];
        foreach (array_keys($keys) as $key){
            if (isset($searched[$key])){
                continue;
            }
            $search_keys[] = $key;
            if ($sp){
                $sp .= ',';
            }
            $sp .= '?';
            $searched[$key] = 1;
        }

        if (0 == count($search_keys)){
            return [];
        }
        $sql = str_replace('###IN###', $sp, $sql);
        $sth = $dbh->prepare($sql);

        $i = 1;
        foreach ($search_keys as $key){
            $sth->bindValue($i, $key);
            $i++;
        }

        $sth->execute();
        $results = $sth->fetchAll();

        $things = [];
        foreach ($results as $result){
            $things[] = $result[$name];
        }

        return $things;
    }
}
