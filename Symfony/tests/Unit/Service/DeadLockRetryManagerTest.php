<?php

namespace App\Tests\Unit\Service;

use App\Service\DeadLockRetryManager;
use App\Tests\Lib\BaseTest;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Statement;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers App\Service\DeadLockRetryManager
 */
class DeadLockRetryManagerTest extends BaseTest
{
    private $mockLogger;

    public function setUp()
    {
        $this->mockLogger = $this->createMock(LoggerInterface::class);

        $this->mockSth = $this
            ->getMockBuilder(Statement::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
    }

    /**
     *
     */
    public function testHappy()
    {
        $dlm = new DeadLockRetryManager($this->mockLogger);

        $this->mockSth->expects($this->once())
            ->method('execute')
            ->with(['foo']);

        $dlm->exec($this->mockSth, ['foo']);
    }

    /**
     *
     */
    public function testRetry()
    {
        $dlm = new DeadLockRetryManager($this->mockLogger);

        $e = $this->createMock(DeadlockException::class);

        $this->mockSth->expects($this->exactly(2))
            ->method('execute');

        $this->mockSth->expects($this->at(0))
            ->method('execute')
            ->with(['foo'])
            ->will($this->throwException($e));

        $this->mockSth->expects($this->at(1))
            ->method('execute')
            ->with(['foo']);

        $dlm->exec($this->mockSth, ['foo']);
    }

    /**
     *
     */
    public function testLimit()
    {
        $dlm = new DeadLockRetryManager($this->mockLogger);

        $e = $this->createMock(DeadlockException::class);

        foreach (range(0, 9) as $i) {

            $this->mockSth->expects($this->at($i))
                ->method('execute')
                ->with(['foo'])
                ->will($this->throwException($e));
        }

        $this->expectException(DeadlockException::class);

        $dlm->exec($this->mockSth, ['foo']);
    }
}
