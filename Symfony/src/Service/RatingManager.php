<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Work;
use App\Service\ScoreManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\RatingRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class RatingManager
{
    private $logger;
    private $em;
    private $repo;
    private $ScoreManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        RatingRepository $repo,
        ScoreManager $scoreManager
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
        $this->scoreManager         = $scoreManager;
    }

    public function setUserWorkRating(
        UserInterface $user,
        Work $work,
        int $stars,
        string $ipaddr,
        string $useragent
    ): array
    {

        $rating = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if (0 === $stars) {
            $this->em->remove($rating);
        }
        else{
            if (null === $rating) {
                $rating = new Rating();
                $this->em->persist($rating);
            }

            $rating
                ->setWork($work)
                ->setUser($user)
                ->setStars($stars)
                ->setIpaddr($ipaddr)
                ->setUseragent($useragent)
            ;
        }

        $this->em->flush();

        list($score, $count) = $this->scoreManager->calculateWorkScore($work);

        return [$rating, $score, $count];
    }
}
