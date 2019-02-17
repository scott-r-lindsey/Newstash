<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Rating;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\RatingRepository
 * @covers App\Entity\Rating
 */
class RatingRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindArrayByUser(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $ratingRepo         = $em->getRepository(Rating::class);

        // build up some sample data ------------------------------------------
        $user = $this->createUser();
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $rating = new Rating();
        $rating->setUser($user)
            ->setWork($work)
            ->setStars(5)
            ->setIpaddr('127.0.0.1')
            ->setUserAgent('Some Browser');

        $em->persist($rating);
        $em->flush();

        // --------------------------------------------------------------------

        $result = $ratingRepo->findArrayByUser($user);

        $this->assertCount(
            1,
            $result
        );

        $this->validUnsetTimestamps($result[0]);

        $this->assertEquals(
            [[
                'id'            => 1,
                'stars'         => 5,
                'ipaddr'        => '127.0.0.1',
                'work_id'       => 1,
                'user_id'       => 1,
                'useragent'     => 'Some Browser'
            ]],
            $result
        );


    }

}
