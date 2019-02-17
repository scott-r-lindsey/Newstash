<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Review;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\ReviewRepository
 * @covers App\Entity\Review
 */
class ReviewRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindReviewByUserAndWork(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $reviewRepo         = $em->getRepository(Review::class);

        list ($user, $review, $work) = $this->createSampleData();

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

    public function testFindArrayByUser(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $reviewRepo         = $em->getRepository(Review::class);

        list ($user, $review, $work) = $this->createSampleData();

        $result = $reviewRepo->findArrayByUser($user);

        $this->assertCount(
            1,
            $result
        );

        $this->validUnsetTimestamps($result[0]);

        $this->assertEquals(
            [[
                "id" => 1,
                "stars" => 5,
                "title" => "Great Book",
                "text" => "Yada Yada Yada",
                "ipaddr" => "127.0.0.1",
                "useragent" => "Internet Explorer (like terrible)",
                "likes" => 0,
                "started_reading_at" => null,
                "finished_reading_at" => null,
                "deleted" => false,
                "user_id" => 1,
                "work_id" => 1
            ]],
            $result
        );

    }

    private function createSampleData(): array
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');

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

        return [$user, $review, $work];
    }

}
