<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Readit;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\ReaditRepository
 * @covers App\Entity\Readit
 */
class ReaditRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindArrayByUser(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $readitRepo         = $em->getRepository(Readit::class);

        // build up some sample data ------------------------------------------
        $user = $this->createUser();
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $weekAgo    = new \DateTime('1 week ago');
        $now        = new \DateTime();

        $readit = new Readit();
        $readit
            ->setUser($user)
            ->setWork($work)
            ->setStatus(100)
            ->setIpaddr('127.0.0.1')
            ->setUserAgent('Some Browser')
            ->setStartedAt($weekAgo)
            ->setFinishedAt($now);

        $em->persist($readit);
        $em->flush();

        // --------------------------------------------------------------------

        $result = $readitRepo->findArrayByUser($user);

        $this->assertCount(
            1,
            $result
        );

        $this->validUnsetDates($result[0], [
            'updated_at',
            'created_at',
            'started_at',
            'finished_at'
        ]);

        $this->assertEquals(
            [[
                'id'            => 1,
                'status'        => 100,
                'ipaddr'        => '127.0.0.1',
                'work_id'       => 1,
                'user_id'       => 1,
                'useragent'     => 'Some Browser'
            ]],
            $result
        );
    }
}
