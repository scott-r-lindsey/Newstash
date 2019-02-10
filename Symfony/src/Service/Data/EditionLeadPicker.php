<?php
declare(strict_types=1);

namespace App\Service\Data;

use App\Entity\Edition;
use App\Service\FormatCache;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EditionLeadPicker
{
    private $logger;
    private $em;


    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
    }

    public function find(
        array $exclude = [],
        int $rescan = 0,
        int $limit = 10000
    ): array
    {

        if ($rescan) {
            $amzn_updated_at = "
                (   edition.amzn_updated_at IS NULL OR
                    edition.amzn_updated_at < DATE_SUB(curdate(), INTERVAL $rescan DAY) )";
        }
        else{
            $amzn_updated_at = 'edition.amzn_updated_at IS NULL';
        }


        $not_in     = '';

        if (count($exclude)) {
            $qm = $this->markers($exclude);
            $not_in     = 'asin NOT IN (' . $qm . ') AND ';
        }

        $sql = "
                SELECT
                    asin, updated_at
                FROM edition
                WHERE
                    $not_in
                    $amzn_updated_at AND
                    apparent_amzn_osi = 0 AND
                    rejected = 0
            ORDER BY updated_at ASC
            LIMIT $limit";

        $dbh = $this->em->getConnection();

        $sth = $dbh->prepare($sql);
        $sth->execute($exclude);

        $results = $sth->fetchAll();

        foreach ($results as $r) {
            $asins[] = (string)$r['asin'];
        }

        return $asins;
    }

    public function findGroomable(): ?string
    {

        $sql = "
            SELECT
                asin
            FROM
                edition
            WHERE
                groomed = 0 AND
                rejected = 0 AND
                amzn_updated_at IS NOT NULL
            ORDER BY
                updated_at ASC
            LIMIT 1";

        $dbh = $this->em->getConnection();

        $sth = $dbh->prepare($sql);
        $sth->execute();

        $results = $sth->fetchAll();

        if (!count($results)) {
            return null;
        }

        return (string)$results[0]['asin'];
    }

    private function markers($ar){
        $str = '';
        foreach ($ar as $a){
            if ($str){
                $str .= ',';
            }
            $str .= '?';
        }
        return $str;
    }
}
