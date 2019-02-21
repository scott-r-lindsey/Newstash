<?php

namespace App\Repository;

use App\Entity\Score;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Score|null find($id, $lockMode = null, $lockVersion = null)
 * @method Score|null findOneBy(array $criteria, array $orderBy = null)
 * @method Score[]    findAll()
 * @method Score[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScoreRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Score::class);
    }

    public function findWorkScore(
        Work $work
    ): Score
    {
        $em = $this->getEntityManager();

        $dql = '
            SELECT s
            FROM App\Entity\Score s
            WHERE s.work = :work';

        $query = $em->createQuery($dql);
        $query->setParameter('work', $work->getId());

        return $query->getSingleResult();
    }
}
