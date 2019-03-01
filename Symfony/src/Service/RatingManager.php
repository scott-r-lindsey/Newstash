<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Rating;
use App\Entity\Work;
use App\Repository\RatingRepository;
use App\Service\Mongo\News;
use App\Service\ScoreManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RatingManager
{
    private $logger;
    private $em;
    private $repo;
    private $scoreManager;
    private $news;

    private $reviewManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        RatingRepository $repo,
        ScoreManager $scoreManager,
        News $news
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
        $this->scoreManager         = $scoreManager;
        $this->news                 = $news;
    }

    public function setReviewManager(
        ReviewManager $reviewManager
    ): void
    {
        $this->reviewManager = $reviewManager;
    }

    public function setUserWorkRating(
        UserInterface $user,
        Work $work,
        int $stars,
        string $ipaddr,
        string $useragent,
        bool $skipReview = false,
        bool $skipNews = false
    ): array
    {

        $new    = false;

        $rating = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if (($rating ) and (0 === $stars)) {
            $this->em->remove($rating);
        }
        else{

            if (null === $rating) {
                $new        = true;
                $rating     = new Rating();
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

        // update the user's review score, if it exists
        if (!$skipReview) {
            $this->reviewManager->updateReviewScore(
                $user,
                $work,
                $stars,
                false
            );
        }

        $this->em->flush();

        list($score, $count) = $this->scoreManager->calculateWorkScore($work);

        if (($new) && (!$skipNews)) {
            $this->news->newRating($rating);
        }

        return [$rating, $score, $count];
    }
}
