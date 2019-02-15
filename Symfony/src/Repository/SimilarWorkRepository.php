<?php

namespace App\Repository;

use App\Entity\SimilarWork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SimilarWork|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimilarWork|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimilarWork[]    findAll()
 * @method SimilarWork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimilarWorkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SimilarWork::class);
    }

    // /**
    //  * @return SimilarWork[] Returns an array of SimilarWork objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SimilarWork
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
