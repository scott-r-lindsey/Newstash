<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class BrowseNodeCache
{

    private $ids        = [];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
    }

    public function getBrowseNodesIds(): array
    {

        if (0 === count($this->ids)) {

            $dbh        = $this->em->getConnection();
            $sql        = 'SELECT DISTINCT id FROM browsenode';
            $sth        = $dbh->query($sql);

            foreach ($sth->fetchAll() as $r){
                $this->ids[$r['id']] = 1;
            }
        }

        return $this->ids;
    }
}
