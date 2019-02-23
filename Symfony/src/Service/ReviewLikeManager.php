<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Review;
use App\Entity\ReviewLike;
use App\Repository\ReviewRepository;
use App\Repository\ReviewLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ReviewLikeManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ReviewLikeRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function createUserReviewLike(
        UserInterface $user,
        Review $review,
        string $ipaddr,
        string $useragent
    ): ReviewLike
    {
        $reviewLike = new ReviewLike();
        $reviewLike
            ->setReview($review)
            ->setUser($user)
            ->setUseragent($useragent)
            ->setIpaddr($ipaddr)
        ;
        $this->em->persist($reviewLike);

        $this->updateReviewLikes($review, 1);

        return $reviewLike;
    }

    public function deleteUserReviewLike(
        UserInterface $user,
        Review $review
    ): void
    {
        $reviewLike = $this->repo->findOneBy([
            'review'    => $review,
            'user'      => $user
        ]);

        $this->updateReviewLikes($review, -1);
    }

    public function updateReviewLikes(
        Review $review,
        int $modifier = 0
    ): void
    {
        $count = $this->repo->count(['review' => $review]);

        $review->setLikes($count + $modifier);
        $this->em->flush();
    }
}
