<?php

namespace App\Tests\Functional\Service;

use App\Entity\Edition;
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

        $this->assertTrue(true);


    }
}
