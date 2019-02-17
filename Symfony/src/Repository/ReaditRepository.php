<?php

namespace App\Repository;

use App\Entity\Readit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
