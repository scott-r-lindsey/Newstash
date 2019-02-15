<?php

namespace App\Repository;

use App\Entity\BrowseNode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BrowseNode|null find($id, $lockMode = null, $lockVersion = null)
 * @method BrowseNode|null findOneBy(array $criteria, array $orderBy = null)
 * @method BrowseNode[]    findAll()
 * @method BrowseNode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrowseNodeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BrowseNode::class);
    }

    // /**
    //  * @return BrowseNode[] Returns an array of BrowseNode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BrowseNode
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
