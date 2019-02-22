<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{

    private $review_counts = [];

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Review::class);
    }


    public function findReviewByUserAndWork(
        int $user_id,
        int $work_id
    ): ?Review
    {

        $em = $this->getEntityManager();

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

        return $query->getOneOrNullResult();
    }

    public function findArrayByUser(UserInterface $user): array
    {

        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
    }
}
