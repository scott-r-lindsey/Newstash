<?php

namespace App\Tests\Functional\Service;

use App\Entity\Edition;
use App\Entity\Readit;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\ReaditManager
 */
class ReaditManagerTest extends BaseTest
{
    protected $DBSetup = true;


    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\ReaditManager');
    }

    public function testSetUserWorkReadit()
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
        // --------------------------------------------------------------------

        $result = $readitRepo->findAll();

        $this->assertCount(
            0,
            $result
        );

        // --------------------------------------------------------------------

        $this->manager->setUserWorkReadit(
            $user,
            $work,
            1,
            '127.0.0.1',
            'Some Browser'
        );

        $result = $readitRepo->findAll();
        $readit = $result[0];

        $this->assertEquals(
            1,
            $readit->getStatus()
        );

        $this->assertEquals(
            null,
            $readit->getFinishedAt()
        );

        // --------------------------------------------------------------------


        $this->manager->setUserWorkReadit(
            $user,
            $work,
            3,
            '127.0.0.1',
            'Some Browser'
        );

        $result = $readitRepo->findAll();

        $this->assertCount(
            1,
            $result
        );

        $this->assertEquals(
            3,
            $readit->getStatus()
        );

        $this->assertInstanceOf(
            \DateTime::class,
            $readit->getFinishedAt()
        );
    }
}
