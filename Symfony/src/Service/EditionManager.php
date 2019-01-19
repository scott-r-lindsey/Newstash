<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class EditionManager
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

    public function stubEditions(
        array $asins
    ):void
    {

        $dbh = $this->em->getConnection();

        $sql = '
            INSERT IGNORE INTO edition
                (asin, created_at)
            VALUES
                (?, ?)';

        $sth = $dbh->prepare($sql);

        foreach ($asins as $asin) {
            $sth->execute([
                $asin,
                date('Y-m-d H:i:s', strtotime('now'))
            ]);
        }
    }

}
