<?php

namespace App\Tests\Functional\Service;

use App\Entity\Edition;
use App\Entity\Review;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class ReviewLikeManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\ReviewLikeManager');
    }

    public function testBasic()
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');
        $reviewLikeManager  = self::$container->get('test.App\Service\ReviewLikeManager');

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

        // rating must be present first
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
        $em->flush();

        // --------------------------------------------------------------------

        $reviewLike = $reviewLikeManager->createUserReviewLike(
            $user,
            $review,
            '123.456',
            'IE 12; like Blink'
        );

        $em->refresh($review);

        $this->assertEquals(
            1,
            $review->getLikes()
        );

        // --------------------------------------------------------------------

        $reviewLikeManager->deleteUserReviewLike(
            $user,
            $review
        );

        $em->refresh($review);

        $this->assertEquals(
            0,
            $review->getLikes()
        );

        // --------------------------------------------------------------------
    }
}
