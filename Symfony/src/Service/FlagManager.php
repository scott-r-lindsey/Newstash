<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Flag;
use App\Entity\Reason;
use App\Entity\Review;
use App\Repository\FlagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FlagManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        FlagRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function setRatingManager(
        RatingManager $ratingManager
    ): void
    {
        $this->ratingManager = $ratingManager;
    }

    public function createUserReviewFlag(
        UserInterface $user,
        Review $review,
        string $message,
        string $useragent,
        string $ipaddr,
        Reason $reason = null
    ): Flag
    {

        $flag = $this->repo->findOneBy([
            'review'    => $review,
            'user'      => $user
        ]);

        if (!$flag) {

            $flag = new Flag();
            $flag
                ->setUser($user)
                ->setReview($review)
            ;
            $this->em->persist($flag);
        }

        $flag
            ->setReason($reason)
            ->setMessage($message)
            ->setIpaddr($ipaddr)
            ->setUseragent($useragent)
        ;

        $this->em->flush();

        return $flag;
    }
}
