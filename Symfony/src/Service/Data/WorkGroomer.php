<?php
declare(strict_types=1);

namespace App\Service\Data;

use App\Entity\Edition;
use App\Service\Data\FrontFinder;
use App\Service\DeadLockRetryManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Timer\Timer;

class WorkGroomer
{

    private $logger;
    private $em;
    private $frontFinder;
    private $dml;

    private $asins                  = [];
    private $sigs                   = [];

    private $searched_asins         = [];
    private $searched_sigs          = [];
    private $searched_aws_asins     = [];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        FrontFinder $frontFinder,
        DeadLockRetryManager $dlm
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->frontFinder          = $frontFinder;
        $this->dlm                  = $dlm;
    }

    public function workGroom(Edition $edition): void
    {
        $this->workGroomLogic($edition->getAsin());
    }

    public function workGroomLogic(string $initialAsin): void
    {
        $em     = $this->em;
        $dbh    = $em->getConnection();
        $ff     = $this->frontFinder;
        $now    = new \DateTime('now');

        Timer::start();             // ------>
        $sql = '
            SELECT
                asin, sig
            FROM
                edition
            WHERE
                asin = ?';

        $sth    = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$initialAsin]);
        $this->timerStop($sql);     // <------

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

        Timer::start();             // ------>
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
        $this->dlm->exec($sth, array_keys($this->asins));
        $this->timerStop($sql);     // <------

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

            Timer::start();             // ------>
            foreach ($work_ids as $work_id){
                $sql = '
                    UPDATE
                        work
                    SET
                        updated_at = ?,
                        front_edition_asin = NULL,
                        deleted = 1
                    WHERE
                        work.id = ?';

                $sth = $dbh->prepare($sql);
                $this->dlm->exec($sth, [
                    $now->format('Y-m-d H:i:s'),
                    $work_id]);
            $this->timerStop($sql);     // <------
            }

            $this->setEditionGroomed($initialAsin);
            return;
        }

        // -------------------------------------------------------------------------
        // create, continue, or combine work records

        // work not found, create one
        if (0 === count(array_keys($work_ids))){

            Timer::start();             // ------>
            $sql = '
                INSERT INTO work
                    (front_edition_asin, title, created_at, updated_at)
                VALUES
                    (?,?,?,?)
                ';

            $sth = $dbh->prepare($sql);
            $this->dlm->exec($sth, [
                $front['asin'],
                $front['title'],
                $now->format('Y-m-d H:i:s'),
                $now->format('Y-m-d H:i:s')]);
            $this->timerStop($sql);     // <------

            $master_work_id = $dbh->lastInsertId();
        }
        // just one
        else if (1 === count(array_keys($work_ids))){
            $keys = array_keys($work_ids);
            $master_work_id = $keys[0];

            // update title, updated_at, front

            Timer::start();             // ------>
            $sql = '
                UPDATE
                    work
                SET
                    title=?, updated_at=?, front_edition_asin=?
                WHERE
                    work.id = ?';

            $sth = $dbh->prepare($sql);
            $this->dlm->exec($sth, [
                $front['title'],
                $now->format('Y-m-d H:i:s'),
                $front['asin'],
                $master_work_id]);
            $this->timerStop($sql);     // <------
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

            Timer::start();             // ------>
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
            $this->dlm->exec($sth, array_keys($work_ids));
            $this->timerStop($sql);     // <------
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

            Timer::start();             // ------>
            $sql = "
                UPDATE work
                SET superseding_id=?, updated_at=?
                WHERE id IN ($in)
            ";
            $sth = $dbh->prepare($sql);
            $this->dlm->exec($sth, array_merge(
                [$master_work_id],
                [$now->format('Y-m-d H:i:s')],
                array_keys($bad_work_ids)
            ) );
            $this->timerStop($sql);     // <------
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
        Timer::start();             // ------>
        $sql = "
            UPDATE
                edition
            SET
                work_id = ?, updated_at = ?
            WHERE
                asin IN ($in)
        ";

        $sth = $dbh->prepare($sql);

        $this->dlm->exec($sth, array_merge(
            [$master_work_id],
            [$now->format('Y-m-d H:i:s')],
            $asins
        ));
        $this->timerStop($sql);     // <------

        // -------------------------------------------------------------------------
        // undelete works which have gained editions

        Timer::start();             // ------>
        $sql = '
            UPDATE
                work
            SET
                updated_at = ?,
                deleted = 0
            WHERE
                work.id = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [
            $now->format('Y-m-d H:i:s'),
            $master_work_id]);
        $this->timerStop($sql);     // <------

        // -------------------------------------------------------------------------

        $this->updateSimilarWorks($asins, (int)$master_work_id);

        $this->setEditionsGroomed((int)$master_work_id);
    }

    private function updateSimilarWorks(
        array $asins,
        int $master_work_id
    ): void
    {
        $em     = $this->em;
        $dbh    = $em->getConnection();

        $in         = '';
        foreach ($asins as $a) {
            if ($in){
                $in .= ',';
            }
            $in .= '?';
        }

        Timer::start();             // ------>
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
        $this->dlm->exec($sth, $asins);
        $this->timerStop($sql);     // <------

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
            Timer::start();             // ------>
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
            $this->dlm->exec($sth, $asins);
            $this->timerStop($sql);     // <------

            $similar_work_ids = [];
            foreach ($sth->fetchAll() as $r){
                if ($r['work_id']){
                    $similar_work_ids[] = $r['work_id'];
                }
            }
        }

        Timer::start();             // ------>
        $sql = '
            DELETE FROM similar_work
            WHERE work_id = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$master_work_id]);
        $this->timerStop($sql);     // <------

        if (count($similar_work_ids)){
            Timer::start();             // ------>
            $sql = '
                INSERT INTO similar_work (work_id, similar_id)
                VALUES (?,?)';

            $sth = $dbh->prepare($sql);
            foreach ($similar_work_ids as $similar_work_id){
                $this->dlm->exec($sth, [$master_work_id, $similar_work_id]);
            }
            $this->timerStop($sql);     // <------
        }
    }

    private function findNewSigAsins(): bool
    {
        // returns true if any new asins are found
        // backstops against $this->searched_sigs

        Timer::start();             // ------>
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
        $this->timerStop($sql);     // <------

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

        Timer::start();             // ------>
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
        $this->timerStop($sql);     // <------

        foreach ($sigs as $sig){
            if (!isset($this->sigs[$sig])){
                $this->sigs[$sig] = 1;
                $new = true;
            }
        }
        return $new;
    }

    private function setEditionGroomed(string $asin): void
    {
        $dbh    = $this->em->getConnection();

        Timer::start();             // ------>
        $sql = '
            UPDATE edition
            SET
                groomed = 1
            WHERE
                asin = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$asin]);
        $this->timerStop($sql);     // <------
    }

    private function setEditionsGroomed(int $work_id): void
    {
        $dbh    = $this->em->getConnection();

        Timer::start();             // ------>
        $sql = '
            UPDATE edition
            SET
                groomed = 1
            WHERE
                work_id = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$work_id]);
        $this->timerStop($sql);     // <------
    }

    private function findAWSRelated(): bool
    {
        // returns true if any new asins are found
        // backstops against $this->searched_aws_asins

        $dbh    = $this->em->getConnection();

        Timer::start();             // ------>
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
        $this->timerStop($sql);     // <------
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

    private function timerStop(string $sql): void
    {
        $time = Timer::stop();

        $this->logger->debug(Timer::secondsToTimeString($time) . ': ' . $sql);
    }
}
