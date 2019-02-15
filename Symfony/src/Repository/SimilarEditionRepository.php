<?php

namespace App\Repository;

use App\Entity\SimilarEdition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SimilarEdition|null find($id, $lockMode = null, $lockVersion = null)
 * @method SimilarEdition|null findOneBy(array $criteria, array $orderBy = null)
 * @method SimilarEdition[]    findAll()
 * @method SimilarEdition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimilarEditionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SimilarEdition::class);
    }

    // /**
    //  * @return SimilarEdition[] Returns an array of SimilarEdition objects
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
    public function findOneBySomeField($value): ?SimilarEdition
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
