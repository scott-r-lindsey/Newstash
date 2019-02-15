<?php

namespace App\Repository;

use App\Entity\Readit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Readit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Readit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Readit[]    findAll()
 * @method Readit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReaditRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Readit::class);
    }

    // /**
    //  * @return Readit[] Returns an array of Readit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Readit
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
