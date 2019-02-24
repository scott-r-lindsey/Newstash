<?php

namespace App\Tests\Functional\Service;

use App\Entity\Edition;
use App\Entity\Rating;
use App\Entity\Review;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class ReviewManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\ReviewManager');
    }

    public function testSetUserWorkReview(): void
    {

        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');
        $reviewManager      = self::$container->get('test.App\Service\ReviewManager');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

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

        $reviewManager->setUserWorkReview(
            $user,
            $work,
            'Great Book',
            'I liked it',
            '123.456',
            'IE 12; like Blink'
        );

        // --------------------------------------------------------------------

        // verify rating
        $rating = $em->getRepository(Rating::class)->findOneBy([]);
        $this->assertEquals(
            [
                5,
                '432.123',
                'Mozilla 14'
            ],
            [
                $rating->getStars(),
                $rating->getIpaddr(),
                $rating->getUseragent()
            ]
        );

        // verify review
        $review = $em->getRepository(Review::class)->findOneBy([]);
        $this->assertEquals(
            [
                5,
                'Great Book',
                'I liked it',
                '123.456',
                'IE 12; like Blink',
            ],
            [
                $review->getStars(),
                $review->getTitle(),
                $review->getText(),
                $review->getIpaddr(),
                $review->getUseragent()
            ]
        );

        // verify one news item
        $news = $news->getNews([]);

        $this->assertCount(
            1,
            $news
        );

        $this->assertEquals(
            'review',
            $news[0]['type']

        );
    }
}
