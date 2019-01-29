<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\EditionManager;

class LeadManager
{
    private $logger;
    private $em;
    private $editionManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        EditionManager $editionManager
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->editionManager       = $editionManager;
    }

    public function newLeads(array $asins): void
    {
        $dbh = $this->em->getConnection();

        // --------------------------------------------------------------------

        $sql = '
            INSERT IGNORE INTO xlead
                (asin, created_at, updated_at, new)
            VALUES
                (?, ?, ?, 1)';
        $sth = $dbh->prepare($sql);

        foreach ($asins as $asin){
            $sth->execute([
                $asin,
                date('Y-m-d H:i:s', strtotime('now')),
                date('Y-m-d H:i:s', strtotime('now'))
            ]);
        }

        // --------------------------------------------------------------------

        $this->editionManager->stubEditions($asins);
    }

    public function leadFollowed(
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
                asin = ?';

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
