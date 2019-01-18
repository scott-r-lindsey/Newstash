<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class LeadManager
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

    private function newLeads(array $asins): void
    {
        $dbh = $this->em->getConnection();

        $sql = '
            INSERT IGNORE INTO xlead
                (id, created_at, updated_at, new)
            VALUES
                (?, ?, ?, 1)';
        $sth = $dbh->prepare($sql);

        foreach ($asins as $asin){
            $bind = array(
                $asin,
                date('Y-m-d H:i:s', strtotime('now')),
                date('Y-m-d H:i:s', strtotime('now')));

            $sth->execute($bind);
        }
    }

    private function leadFollowed(
        string $asin,
        bool $rejected = false,
        string $amzn_format = null
    ): void
    {
        $dbh = $this->em->getConnection();

        $sql = '
            UPDATE xlead SET
                new = 0,
                updated_at = ?,
                rejected = ?,
                amzn_format = ?
            WHERE
                id = ?';

        $sth = $dbh->prepare($sql);
        $bind = array(
            date('Y-m-d H:i:s', strtotime('now')),
            $rejected ? 1 : 0,
            $amzn_format,
            $asin,
        );
        $sth->execute($bind);
    }
}
