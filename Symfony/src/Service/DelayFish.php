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

            $nap_secs = $this->delay - ($now - $this->last);

            $this->logger->info("Sleeping for $nap_secs seconds (of " . (string)$this->delay .")");
            $time_start = microtime_float();
            usleep ((int)($nap_secs * 1000000));
            $time_end = microtime_float();
            $time = $time_end - $time_start;
            $this->logger->info("Finished Sleeping for $nap_secs seconds (of " . (string)$this->delay ."), actual $time");
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
}
