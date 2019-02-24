<?php

namespace App\Repository;

use App\Entity\Flag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Flag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Flag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Flag[]    findAll()
 * @method Flag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FlagRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Flag::class);
    }

    public function findOutstandingFlags(): array
    {
        $em = $this->getEntityManager();

        $dql = '
            SELECT f, r, c
            FROM App\Entity\Flag f
            LEFT JOIN f.comment r
            LEFT JOIN f.review c
            WHERE
                f.sorted = 0
            ORDER BY f.created_at ASC
        ';

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
