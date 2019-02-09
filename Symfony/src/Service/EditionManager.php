<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Service\DeadLockRetryManager;

class EditionManager
{
    private $logger;
    private $em;
    private $dml;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        DeadLockRetryManager $dlm
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->dlm                  = $dlm;
    }

    public function stubEdition(string $asin): void
    {
        $this->stubEditions([$asin]);
    }

    public function stubEditions(
        array $asins
    ):void
    {

        $dbh = $this->em->getConnection();

        $sql = '
            INSERT IGNORE INTO edition
                (asin, created_at, updated_at)
            VALUES
                (?, ?, ?)';

        $sth = $dbh->prepare($sql);

        foreach ($asins as $asin) {
            $this->dlm->exec($sth, [
                $asin,
                date('Y-m-d H:i:s', strtotime('now')),
                date('Y-m-d H:i:s', strtotime('now'))
            ]);
        }
    }

    public function similarUpdate(
        string $asin,
        array $asins): void
    {

        $dbh = $this->em->getConnection();

        // clear out old records
        $sql = '
            DELETE FROM similar_edition
            WHERE edition_asin = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$asin]);

        // insert new records
        $sql = '
            INSERT IGNORE INTO similar_edition
                (edition_asin, similar_asin, xrank)
            VALUES
                (?,?,?)';
        $sth = $dbh->prepare($sql);

        $rank = 1;
        foreach ($asins as $a) {
            $this->dlm->exec($sth, [$asin, $a, $rank]);
            $rank++;
        }
    }

    public function markRejected(string $asin): void
    {
        $dbh = $this->em->getConnection();

        $sql = '
            UPDATE edition
            SET rejected = 1
            WHERE edition_asin = ?';

        $sth = $dbh->prepare($sql);
        $this->dlm->exec($sth, [$asin]);
    }
}
