<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\ReviewRepository;

class ReviewManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ReviewRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

}
