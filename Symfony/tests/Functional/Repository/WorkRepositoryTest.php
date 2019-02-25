<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Rating;
use App\Entity\Readit;
use App\Entity\Review;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * @covers App\Repository\WorkRepository
 */
class WorkRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindUserStatusCountAndWorks(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');
        $reviewLikeManager  = self::$container->get('test.App\Service\ReviewLikeManager');
        $workRepository     = $em->getRepository(Work::class);

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $user               = $this->createUser();
        $user
            ->setFirstName('joe')
            ->setLastName('blow')
        ;

        $em->flush();
        $em->refresh($edition);
        $work = $edition->getWork();

        $readit = new Readit();
        $readit
            ->setUser($user)
            ->setWork($work)
            ->setStatus(1)
            ->setIpaddr('432.123')
            ->setUseragent('Mozilla 14')
            ->setStartedAt(new DateTime())
        ;
        $em->persist($readit);

        $review = new Review();
        $review
            ->setUser($user)
            ->setWork($work)
            ->setStars(5)
            ->setIpaddr('432.123')
            ->setUseragent('Mozilla 14')
            ->setTitle('title')
            ->setText('text')
        ;
        $em->persist($review);

        $rating = new Rating();
        $rating
            ->setUser($user)
            ->setWork($work)
            ->setStars(5)
            ->setIpaddr('432.123')
            ->setUseragent('Mozilla 14')
        ;
        $em->persist($rating);
        $em->flush();

        // --------------------------------------------------------------------

        list ($count, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            'toread',
            1,
            20,
            'alpha',
            true
        );

        $this->assertEquals(
            1,
            $count
        );

        $this->assertEquals(
            [$work],
            $works
        );

        // --------------------------------------------------------------------

        list ($count, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            'reading',
            1,
            20,
            'bestseller',
            true
        );

        $this->assertEquals(
            0,
            $count
        );

        $this->assertEquals(
            [],
            $works
        );

        // --------------------------------------------------------------------

        list ($count, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            'readit',
            1,
            20,
            'added',
            true
        );

        $this->assertEquals(
            0,
            $count
        );

        $this->assertEquals(
            [],
            $works
        );

        // --------------------------------------------------------------------

        list ($count, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            'ratings',
            1,
            20,
            'pubdate',
            true
        );

        $this->assertEquals(
            1,
            $count
        );

        $this->assertEquals(
            [$work],
            $works
        );

        // --------------------------------------------------------------------

        list ($count, $works) = $workRepository->findUserStatusCountAndWorks(
            $user,
            'reviews',
            1,
            20,
            'pubdate',
            true
        );

        $this->assertEquals(
            1,
            $count
        );

        $this->assertEquals(
            [$work],
            $works
        );
    }

    public function testFindByIsbn(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $workRepository     = $em->getRepository(Work::class);

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        // --------------------------------------------------------------------

        $work = $workRepository->findByIsbn('9780674979857');

        $this->assertEquals(
            '0674979850',
            $work->getFrontEdition()->getAsin()
        );

        // --------------------------------------------------------------------

        $work = $workRepository->findByIsbn('0000000000000');

        $this->assertEquals(
            null,
            $work
        );
    }
}
