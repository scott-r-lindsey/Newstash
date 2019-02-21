<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Readit;
use App\Entity\Work;
use App\Repository\ReaditRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReaditManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ReaditRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function setUserWorkReadit(
        UserInterface $user,
        Work $work,
        int $status,
        string $ipaddr,
        string $useragent
    ): Readit
    {

        $readit = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if (null === $readit) {

            $readit = new Readit();
            $readit
                ->setWork($work)
                ->setUser($user)
                ->setStartedAt(new \DateTime()
            );

            $this->em->persist($readit);
        }

        if (3 === $status){
            $readit->setFinishedAt(new \DateTime());
        }
        else{
            $readit->setFinishedAt(null);
        }

        $readit
            ->setStatus($status)
            ->setIpaddr($ipaddr)
            ->setUseragent($useragent)
        ;

        $this->em->flush();

        return $readit;
    }
}
