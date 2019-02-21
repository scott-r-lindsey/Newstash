<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Score;
use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Repository\ScoreRepository;

class ScoreManager
{
    private $logger;
    private $em;
    private $repo;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ScoreRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function calculateWorkScore(
        Work $work,
        bool $flush = true
    ): void
    {

        $em = $this->em;

        $dql = '
            SELECT r
            FROM App\Entity\Rating r
            JOIN r.user u
            WHERE
                r.work = :work AND
                u.locked = 0';

        $query = $em->createQuery($dql);
        $query->setParameter('work', $work);
        $ratings = $query->getArrayResult();

        $stats = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        );

        $average = $count = $total = 0;

        foreach ($ratings as $r){
            if (!isset($stats[$r['stars']])){
                continue;
            }
            $stats[$r['stars']]++;
            $total += $r['stars'];
            $count++;
        }
        if (0 != $count){
            $average = round($total / $count, 2);
        }

        // --------------------------------------------------------------------

        $dql = '
            DELETE FROM
                App\Entity\Score s
            WHERE
                s.work = :work
        ';

        $query = $em->createQuery($dql);
        $query->setParameter('work', $work);
        $query->execute();

        // --------------------------------------------------------------------

        $score = new Score();
        $score->setWork($work)
            ->setScore((string)$average)
            ->setTotal($count)
            ->setOnes($stats[1])
            ->setTwos($stats[2])
            ->setThrees($stats[3])
            ->setFours($stats[4])
            ->setFives($stats[5]);

        $em->persist($score);
        if ($flush) {
            $em->flush();
        }
    }
}
