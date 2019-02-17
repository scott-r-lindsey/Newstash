<?php

namespace App\Repository;

use App\Entity\ReviewLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method ReviewLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReviewLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReviewLike[]    findAll()
 * @method ReviewLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewLikeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReviewLike::class);
    }


    public function findByUserAndWork(
        int $work_id,
        int $user_id
    ): array
    {

        $em = $this->getEntityManager();

        $dql = '
            SELECT rl
            FROM
                App\Entity\ReviewLike rl
            JOIN rl.review r
            WHERE
                rl.user = :user AND
                r.work = :work';

        $query = $em->createQuery($dql);
        $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->setParameter('work', $work_id)
            ->setParameter('user', $user_id);

        return $query->getArrayResult();
    }
}
