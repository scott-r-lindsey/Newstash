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

    public function findByIdWithChildren(array $ids)
    {
        $qb = $this->createQueryBuilder('b');
        $qb
            ->addSelect('c')
            ->leftJoin('b.children', 'c')
            ->add('where', $qb->expr()->in('b.id', ':ids'))
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }
}
