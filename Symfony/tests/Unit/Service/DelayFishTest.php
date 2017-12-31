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

        $this->mockLogger->expects($this->never())
            ->method($this->anything());

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

        $this->mockLogger->expects($this->once())
            ->method('info')
            ->with($this->matchesRegularExpression(
                '/^Sleeping for 0.099[\d]+ seconds$/'
            ));

        $now = microtime(true);

        $fish->delay();
        $fish->delay();

        $this->AssertGreaterThan(.1, (microtime(true) - $now));
        $this->AssertLessThan(0.11, (microtime(true) - $now));
    }
}
