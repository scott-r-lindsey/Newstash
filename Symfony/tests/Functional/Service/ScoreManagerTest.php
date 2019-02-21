<?php
declare(strict_types=1);

namespace App\Tests\Functional\Service;

use App\Entity\Rating;
use App\Entity\Score;
use App\Entity\User;
use App\Entity\Work;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class ScoreManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\ScoreManager');
    }

    public function testBasic()
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $scoreRepo          = $em->getRepository(Score::class);

        // build up some sample data ------------------------------------------
        $edition        = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $user           = $this->createUser();
        $locked_user    = $this->createUser();
        $locked_user->setLocked(true);

        $em->flush();
        $em->refresh($edition);
        $work = $edition->getWork();
        // --------------------------------------------------------------------

        // - null score -------------------------------------------------------
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'0',0,0,0,0,0,0);

        // - add an invalid rating --------------------------------------------
        $this->addRating($work, $user, 0);
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'0',0,0,0,0,0,0);

        // - add a locked users rating to ignore ------------------------------
        $this->addRating($work, $locked_user, 0);
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'0',0,0,0,0,0,0);

        // - add one five star rating -----------------------------------------
        $this->addRating($work, $user, 5);
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'5',1,0,0,0,0,1);

        // - add one two star rating -----------------------------------------
        $this->addRating($work, $user, 2);
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'3.5',2,0,1,0,0,1);

        // - add a three star rating -----------------------------------------
        $this->addRating($work, $user, 3);
        $this->manager->calculateWorkScore($work);
        $score = $scoreRepo->findWorkScore($work);
        $this->validateScore($score,'3.33',3,0,1,1,0,1);
    }

    private function addRating(
        Work $work,
        User $user,
        int $stars
    ): void
    {
        $em = $this->em;

        $rating = new Rating();
        $rating->setWork($work)
            ->setUser($user)
            ->setStars($stars)
            ->setIpaddr('123.456')
            ->setUseragent('IE 12; like Blink');

        $em->persist($rating);
        $em->flush();
    }

    private function validateScore(
        Score $score,
        string $average,
        int $total,
        int $ones,
        int $twos,
        int $threes,
        int $fours,
        int $fives
    ): void
    {
        $this->assertEquals($average, $score->getScore());
        $this->assertEquals($total, $score->getTotal());
        $this->assertEquals($ones, $score->getOnes());
        $this->assertEquals($twos, $score->getTwos());
        $this->assertEquals($threes, $score->getThrees());
        $this->assertEquals($fours, $score->getFours());
        $this->assertEquals($fives, $score->getFives());
    }

}
