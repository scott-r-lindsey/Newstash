<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 *
 */

class DelayFish{

    const SLOWDOWN = .5;
    const SPEEDUP = .1;

    private $minDelay;
    private $delay;
    private $last;

    public function __construct(
        LoggerInterface $logger,
        float $minDelay
    ){
        $this->delay        = $minDelay;
        $this->minDelay     = $minDelay;
        $this->logger       = $logger;
    }


    public function delay(): void
    {

        $now = microtime(true);

        if (($this->last + $this->delay) > $now) {

            $secondsToSleep = $this->delay - ($now - $this->last);

            $this->logger->info("Sleeping for $secondsToSleep seconds of " . (string)$this->delay);
            $this->sleep($secondsToSleep);

        }

        $this->last = microtime(true);

        $this->speedUp();
    }

    public function speedUp(): void
    {
        $period = $this->delay - ($this->minDelay * self::SPEEDUP);
        if ($period < $this->minDelay) {
            $this->delay = $this->minDelay;
            $this->logger->debug('Delay set to ' . (string)$this->delay);
        }
        else{
            $this->delay = $period;
            $this->logger->debug('Delay restored to default ' . (string)$this->delay);
        }
    }

    public function slowDown(): void
    {
        $this->delay += ($this->minDelay * self::SLOWDOWN);
        $this->logger->info('Delay increase to ' . (string)$this->delay);
    }

    public function getCurrentDelay(): float
    {
        return $this->delay;
    }
    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * Sometimes usleep is sleeping less than expected?  I think signals
     * from forked children cause sleep to end.  In any case, this
     * will keep sleeping until we have napped the desired period.
     */
    private function sleep(float $sleepSeconds): void
    {
        $startSeconds   = microtime(true);
        $μsSlept        = 0;

        $μsToSleep      = (int)($sleepSeconds * 1000000);

        while ($μsToSleep > $μsSlept) {

            $μsToSleepNow = $μsToSleep - $μsSlept;
            $this->logger->info("Sleeping for $μsToSleepNow μs, already slept $μsSlept μs");

            usleep ($μsToSleepNow);

            $endSeconds     = microtime(true);
            $secondsSlept   = $endSeconds - $startSeconds;

            $μsSlept = (int)($secondsSlept * 1000000);

            $this->logger->info("I slept for $secondsSlept, or $μsSlept μs");
        }
    }
}
