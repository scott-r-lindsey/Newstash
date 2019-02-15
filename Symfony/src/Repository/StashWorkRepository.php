<?php

namespace App\Repository;

use App\Entity\StashWork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StashWork|null find($id, $lockMode = null, $lockVersion = null)
 * @method StashWork|null findOneBy(array $criteria, array $orderBy = null)
 * @method StashWork[]    findAll()
 * @method StashWork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StashWorkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StashWork::class);
    }

    // /**
    //  * @return StashWork[] Returns an array of StashWork objects
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
    public function findOneBySomeField($value): ?StashWork
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
