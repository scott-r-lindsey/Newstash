<?php

namespace App\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Service\DelayFish;
use Psr\Log\LoggerInterface;

/**
 * @covers App\Service\DelayFish
 */
class DelayFishTest extends BaseTest
{
    private $mockLogger;

    public function setUp()
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);
    }

    /**
     *
     */
    public function testZeroDelay()
    {
        $fish = new DelayFish($this->mockLogger, 5);

        $now = microtime(true);
        $fish->delay();
        $this->AssertLessThan(.01, (microtime(true) - $now));
    }

    /**
     *
     */
    public function testRealDelay()
    {
        $fish = new DelayFish($this->mockLogger, 0.1);

        $now = microtime(true);

        $fish->delay();
        $fish->delay();

        $this->AssertGreaterThan(.1, (microtime(true) - $now));
        $this->AssertLessThan(0.11, (microtime(true) - $now));
    }

    public function testSlowDownAndSpeedup(): void
    {
        $fish = new DelayFish($this->mockLogger, 1);

        $this->assertEquals(
            1,
            $fish->getCurrentDelay()
        );

        $fish->speedUp();

        $this->assertEquals(
            1,
            $fish->getCurrentDelay()
        );

        $fish->slowDown();

        $this->assertEquals(
            1.5,
            $fish->getCurrentDelay()
        );

        $fish->speedUp();

        $this->assertEquals(
            1.4,
            $fish->getCurrentDelay()
        );


        $fish->speedUp();
        $fish->speedUp();
        $fish->speedUp();
        $fish->speedUp();
        $fish->speedUp();

        $this->assertEquals(
            1,
            $fish->getCurrentDelay()
        );
    }
}
