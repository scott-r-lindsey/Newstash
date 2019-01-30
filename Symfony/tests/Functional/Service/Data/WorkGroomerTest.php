<?php

namespace App\Tests\Functional\Service\Apa;

use App\Entity\Edition;
use App\Entity\Format;
use App\Entity\Work;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers App\Service\Data\WorkGroomer
 */
class WorkGroomerTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $now                = new \DateTime('now');
        $just_now           = new \DateTime('5 seconds ago');
        $em                 = self::$container->get('doctrine')->getManager();
        $editionRepo        = $em->getRepository(Edition::class);
        $workRepo           = $em->getRepository(Work::class);
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');

        $asin1 = 'asin000001';
        $asin2 = 'asin000002';
        $asin3 = 'asin000003';
        $asin4 = 'asin000004';
        $asin5 = 'asin000005';
        $asin6 = 'asin000006';
        $asin7 = 'asin000007';
        $asin8 = 'asin000008';
        $asin9 = 'asin000009';

        $this->insertEdition($asin1, 1, 'a title 1', 'img.img', true, $just_now, 'sig 1', '');
        $this->insertEdition($asin2, 1, 'a title 2', 'img.img', true, $now, 'sig 1', '');

        $workGroomer->workGroomLogic($asin1);

        $one = $editionRepo->findOneByAsin($asin1);
        $two = $editionRepo->findOneByAsin($asin2);

        $this->assertEquals($one->getWork()->getId(), $two->getWork()->getId());
        $this->assertGreaterThan($just_now, $one->getUpdatedAt());
        $this->assertGreaterThan($just_now, $two->getUpdatedAt());
        $this->assertGreaterThan($just_now, $one->getWork()->getUpdatedAt());
        $this->assertGreaterThan($just_now, $two->getWork()->getUpdatedAt());
        $this->assertEquals($one->getWork()->getTitle(), 'a title 2');
        $this->assertEquals($one->getWork()->getFrontEdition()->getAsin(), $two->getAsin());

        // sig -> amzn alt -> sig all must match up
        $work_id    = $this->insertWork('work title');
        $this->insertEdition($asin3, 1, 'front title', 'img.img', true, $now, 'sig 2', '');
        $this->insertEdition($asin4, 1, 'a title 3', 'img.img', true, $just_now, 'sig 2', 'asin000004,asin000005');
        $this->insertEdition($asin5, 1, 'a title 4', 'img.img', true, $just_now, 'sig 3', '');
        $this->insertEdition($asin6, 1, 'a title 5', 'img.img', true, $just_now, 'sig 3', '', $work_id);

        $workGroomer->workGroomLogic($asin3);

        $three      = $editionRepo->findOneByAsin($asin3);
        $four       = $editionRepo->findOneByAsin($asin4);
        $five       = $editionRepo->findOneByAsin($asin5);
        $six        = $editionRepo->findOneByAsin($asin6);

        $this->assertEquals($three->getWork()->getId(), $work_id);
        $this->assertEquals($four->getWork()->getId(), $work_id);
        $this->assertEquals($five->getWork()->getId(), $work_id);
        $this->assertEquals($six->getWork()->getId(), $work_id);
        $this->assertEquals($six->getWork()->getTitle(), 'front title');

        // test the fixing of split works
        $alpha_id   = $this->insertWork('work alpha');
        $beta_id    = $this->insertWork('work beta');
        $charlie_id = $this->insertWork('work charlie');

        $this->insertEdition($asin7, 1, 'alpha title', 'img.img', true, $now, 'sig 4', '', $alpha_id);
        $this->insertEdition($asin8, 1, 'beta title', 'img.img', true, $just_now, 'sig 4', '', $beta_id);
        $this->insertEdition($asin9, 1, 'charlie title', 'img.img', true, $just_now, 'sig 4', '', $charlie_id);

        $workGroomer->workGroomLogic($asin7);

        $seven      = $editionRepo->findOneByAsin($asin7);
        $eight      = $editionRepo->findOneByAsin($asin8);
        $nine       = $editionRepo->findOneByAsin($asin9);

        $this->assertEquals($seven->getWork()->getId(), $alpha_id);
        $this->assertEquals($eight->getWork()->getId(), $alpha_id);
        $this->assertEquals($nine->getWork()->getId(), $alpha_id);

        $alpha      = $workRepo->findOneById($alpha_id);
        $beta       = $workRepo->findOneById($beta_id);
        $charlie    = $workRepo->findOneById($charlie_id);

        $this->assertEquals($beta->getSuperseding()->getId(), $alpha->getId());
        $this->assertEquals($charlie->getSuperseding()->getId(), $alpha->getId());
    }

    public function testSimilarWorks(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();

        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $now                = new \DateTime('now');

        $editionRepo        = $em->getRepository(Edition::class);
        $workRepo           = $em->getRepository(Work::class);


        /* ---------------------------------------------------------------------
            create editions a1, a2 role up to work a
            create editions b1, b2 role up to work b
            create editions c1, c2 role up to work c

            similar_editions:
                a1, b1
                a2, c2

            target similar works:
                a, b
                a, c
        --------------------------------------------------------------------- */

        $asinA1 = 'asin0000a1';
        $asinA2 = 'asin0000a2';
        $asinB1 = 'asin0000b1';
        $asinB2 = 'asin0000b2';
        $asinC1 = 'asin0000c1';
        $asinC2 = 'asin0000c2';

        $this->insertEdition($asinA1, 1, 'edition a1', 'img.img', true, $now, 'sig a', '');
        $this->insertEdition($asinA2, 1, 'edition a2', 'img.img', true, $now, 'sig a', '');

        $this->insertEdition($asinB1, 1, 'edition b1', 'img.img', true, $now, 'sig b', '');
        $this->insertEdition($asinB2, 1, 'edition b2', 'img.img', true, $now, 'sig b', '');

        $this->insertEdition($asinC1, 1, 'edition c1', 'img.img', true, $now, 'sig c', '');
        $this->insertEdition($asinC2, 1, 'edition c2', 'img.img', true, $now, 'sig c', '');

        $this->insertSimilarEditions($asinA1, $asinB1, 1);
        $this->insertSimilarEditions($asinA2, $asinC2, 1);

        $workGroomer->workGroomLogic($asinA1);
        $workGroomer->workGroomLogic($asinB1);
        $workGroomer->workGroomLogic($asinC1);

        $workGroomer->workGroomLogic($asinA1);

        $a1 = $editionRepo->findOneByAsin($asinA1);
        $a2 = $editionRepo->findOneByAsin($asinA2);
        $b1 = $editionRepo->findOneByAsin($asinB1);
        $b2 = $editionRepo->findOneByAsin($asinB2);
        $c1 = $editionRepo->findOneByAsin($asinC1);
        $c2 = $editionRepo->findOneByAsin($asinC2);

        $sims   = $a1->getSimilarEditions();
        $sim    = $sims[0];
        $this->assertEquals($b1->getAsin(), $sim->getSimilar()->getAsin());

        $sims   = $a2->getSimilarEditions();
        $sim    = $sims[0];
        $this->assertEquals($c2->getAsin(), $sim->getSimilar()->getAsin());

        $this->assertEquals($a1->getWork()->getId(), $a2->getWork()->getId());
        $this->assertEquals($b1->getWork()->getId(), $b2->getWork()->getId());
        $this->assertEquals($c1->getWork()->getId(), $c2->getWork()->getId());

        $a = $workRepo->findOneById($a1->getWork()->getId());
        $b = $workRepo->findOneById($b1->getWork()->getId());
        $c = $workRepo->findOneById($c1->getWork()->getId());

        $targetWorks    = [$b->getId(), $c->getId()];
        $simWorks       = $a->getSimilarWorks();
        $this->assertEquals(2, count($simWorks));

        foreach ($simWorks as $simWork){
            $this->assertContains($simWork->getSimilar()->getId(), $targetWorks);
        }

        $this->assertNotEquals(
            $simWorks[0]->getSimilar()->getId(),
            $simWorks[1]->getSimilar()->getId());
    }

    // ------------------------------------------------------------------------

    private function insertWork(string $title)
    {
        $dbh        = self::$container->get('doctrine')->getManager()->getConnection();
        $now        = new \DateTime();

        $sql = '
            INSERT INTO work
                (title, created_at, updated_at)
            VALUES
                (?,?,?)';

        $sth = $dbh->prepare($sql);
        $sth->execute([
            $title,
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s')
        ]);

        return $dbh->lastInsertId();
    }

    private function insertEdition(
        string $asin,
        int $format_id,
        string $title,
        string $amzn_large_cover,
        bool $active,
        \DateTime $releaseDate,
        string $sig,
        string $amzn_alts,
        int $work_id = null
    ): void
    {

        $dbh            = self::$container->get('doctrine')->getManager()->getConnection();
        $now            = new \DateTime();

        $sql = '
            INSERT INTO edition
                (asin, format_id, title, amzn_large_cover, active, release_date,
                sig, amzn_alternatives, work_id, created_at, updated_at, amzn_updated_at)
            VALUES
                (?,?,?,?,?,?,?,?,?,?,?,?)';

        $sth = $dbh->prepare($sql);
        $sth->execute([
            $asin,
            $format_id,
            $title,
            $amzn_large_cover,
            $active,
            $releaseDate->format('Y-m-d H:i:s'),
            $sig,
            $amzn_alts,
            $work_id,
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s'),
            $now->format('Y-m-d H:i:s'),
        ]);
    }

    private function insertSimilarEditions(
        string $edition_id,
        string $similar_id,
        int $rank
    ): void
    {

        $dbh            = self::$container->get('doctrine')->getManager()->getConnection();
        $sql = '
            INSERT INTO similar_edition (edition_asin, similar_asin, xrank)
            VALUES (?,?,?)';

        $sth = $dbh->prepare($sql);
        $sth->execute(array($edition_id, $similar_id, $rank));
    }
}
