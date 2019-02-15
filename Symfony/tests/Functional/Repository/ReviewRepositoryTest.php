<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Review;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\Review
 */
class ReviewRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindReviewByUserAndWork(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $reviewRepo         = $em->getRepository(Review::class);


        // build up some sample data ------------------------------------------
        $user = $this->createUser();
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $review = new Review();
        $review->setUser($user)
            ->setWork($edition->getWork())
            ->setStars(5)
            ->setTitle('Great Book')
            ->setText('Yada Yada Yada')
            ->setIpaddr('127.0.0.1')
            ->setUseragent('Internet Explorer (like terrible)')
            ->setDeleted(false);

        $em->persist($review);
        $em->flush();

        $review = $reviewRepo->findReviewByUserAndWork($user->getId(), $work->getId());

        $this->assertEquals(
            'Great Book',
            $review->getTitle()
        );
    }

    public function testFindReviewByUserAndWorkNull(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $reviewRepo         = $em->getRepository(Review::class);


        // build up some sample data ------------------------------------------
        $user = $this->createUser();
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $review = $reviewRepo->findReviewByUserAndWork($user->getId(), $work->getId());

        $this->assertEquals(
            null,
            $review
        );
    }
}
