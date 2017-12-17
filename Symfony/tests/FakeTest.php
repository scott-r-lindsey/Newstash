<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;

class FakeTest extends BaseTest
{

    public function testTrue()
    {
        $this->assertTrue(true);
    }
    public function testService(): void
    {
        $this->container->get('test.App\Service\BookParser');
        $this->assertTrue(true);
    }

}
