<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Review;
use App\Entity\Work;
use App\Repository\ReviewRepository;
use App\Repository\RatingRepository;
use App\Service\Mongo\News;
use App\Service\RatingManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

class ReviewManager
{
    private $logger;
    private $em;
    private $repo;
    private $ratingRepo;
    private $news;

    private $ratingManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ReviewRepository $repo,
        RatingRepository $ratingRepo,
        News $news
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
        $this->ratingRepo           = $ratingRepo;
        $this->news                 = $news;
    }

    public function setRatingManager(
        RatingManager $ratingManager
    ): void
    {
        $this->ratingManager = $ratingManager;
    }

    public function setUserWorkReview(
        UserInterface $user,
        Work $work,
        string $title,
        string $text,
        string $ipaddr,
        string $useragent,
        DateTime $started = null,
        DateTime $finished = null,
        bool $skipRating = false,
        bool $skipNews = false
    ): Review
    {
        $new    = false;

        $review = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if (null === $review) {
            $new        = true;
            $review     = new Review();
            $this->em->persist($review);
        }

        // get stars from rating
        $rating = $this->ratingRepo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        $review
            ->setWork($work)
            ->setUser($user)
            ->setTitle($title)
            ->setText($text)
            ->setIpaddr($ipaddr)
            ->setUseragent($useragent)
            ->setStartedReadingAt($started)
            ->setFinishedReadingAt($finished)
        ;
        if ($rating) {
            $review->setStars($rating->getStars());
        }

        $this->em->flush();

        if ($new) {
            $this->news->newReview($review);
        }

        return $review;
    }

    public function updateReviewScore(
        UserInterface $user,
        Work $work,
        int $stars,
        bool $flush = true
    ): void
    {
        $review = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        if ($review) {
            $review->setStars($stars);
        }

        if ($flush) {
            $this->em->flush();
        }
    }

    public function deleteUserWorkReview(
        UserInterface $user,
        Work $work
    ): void
    {
        $review = $this->repo->findOneBy([
            'work'  => $work,
            'user'  => $user
        ]);

        $this->em->remove($review);
        $this->em->flush();

        $this->news->removeReview($review);
    }

    public function getReviews(
        $work_id,
        $sort,
        $page,
        $stars = false,
        $user_id = false
    ): array
    {

        $em                 = $this->em;
        $page--;
        $count              = 50;
        $user_review        = false;

        $work_id = (int)$work_id;

        // --------------------------------------------------------------------
        $skip = $count * $page;
        if ($user_id){
            if (0 == $page){
                $dql = '
                    SELECT r, u, rl
                    FROM App\Entity\Review r
                    JOIN r.user u
                    LEFT JOIN r.review_likes rl
                    WHERE
                        r.work = :work AND
                        r.user = :user';
                $query = $em->createQuery($dql);
                $query->setParameter('work', $work_id);
                $query->setParameter('user', $user_id);
                $user_review = $query->getOneOrNullResult();
            }
            else{
                $dql = '
                    SELECT r, u
                    FROM App\Entity\Review r
                    JOIN r.user u
                    WHERE
                        r.work = :work AND
                        r.user = :user';
                $query = $em->createQuery($dql);
                $query->setParameter('work', $work_id);
                $query->setParameter('user', $user_id);
                $user_review = $query->getOneOrNullResult();
                $skip = ($count * $page) -1;
            }
        }

        // --------------------------------------------------------------------

        if ((0 == $page) and ($user_review)){
            $count--;
        }

        $sorts = [
            'new'   => 'r.created_at DESC',
            'old'   => 'r.created_at ASC',
            'liked' => 'r.likes DESC',
        ];
        $sort_sql = $sorts[$sort];

        $user_review_sql = $user_review_dql = '';
        if ($user_review){
            $user_review_dql = 'r.id != :user_review AND';
            $user_review_sql = 'review.id != ? AND';
        }

        $stars_sql = $stars_dql = '';
        if ($stars){
            $stars_dql = 'r.stars = :stars AND';
            $stars_sql = 'review.stars = ? AND';
        }

        $dql = "
            SELECT r,u
            FROM App\Entity\Review r
            JOIN r.user u
            WHERE
                r.deleted = false AND
                $stars_dql
                $user_review_dql
                r.work = :work
            ORDER BY $sort_sql";

        $query = $em->createQuery($dql);

        if ($stars){
            $query->setParameter('stars', $stars);
        }
        if ($user_review){
            $query->setParameter('user_review', $user_review);
        }

        $query->setParameter('work', $work_id)
            ->setMaxResults($count)
            ->setFirstResult($skip);
        $reviews = $query->getResult();

        if ((0 == $page) and ($user_review)){
            array_unshift($reviews, $user_review);
        }

        // --------------------------------------------------------------------

        $sql = "
            SELECT
                count(*) AS count
            FROM
                review
            WHERE
                review.work_id = ? AND
                $stars_sql
                $user_review_sql
                review.deleted = 0";

        $dbh = $em->getConnection();
        $sth = $dbh->prepare($sql);

        $bind = [$work_id];

        if ($stars){
            $bind[] = $stars;
        }
        if ($user_review_sql){
            $bind[] = $user_review->getId();
        }

        $sth->execute($bind);

        $result = $sth->fetch();
        $matches = $result['count'];

        $page++;
        $hasmore = true;
        if (($page * ($count +1)) >= $matches){
            $hasmore = false;
        }

        // --------------------------------------------------------------------

        $review_count       = $this->repo->count([
            'work'      => $work_id,
            'deleted'   => 0
        ]);

        return compact(
            'reviews',
            'review_count',
            'work_id',
            'hasmore',
            'matches',
            'stars',
            'sort',
            'page',
            'user_id'
        );
    }
}
