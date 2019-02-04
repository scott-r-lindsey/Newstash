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


    /*

        the starting delay represents the minDelay
        we maintain another value called "delay"
        slower() adds "minDay * .5 to delay"
        when delay() is called, it waits delay, and then
        decrements delay by .1 * minDelay

    */

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

            $this->logger->info("Sleeping for $nap_secs seconds");

            usleep ((int)($nap_secs * 1000000));
        }

        $this->last = microtime(true);

        $this->speedUp();
    }

    public function speedUp(): void
    {
        $period = $this->delay - ($this->minDelay * self::SPEEDUP);
        if ($period < $this->minDelay) {
            $this->delay = $this->minDelay;
        }
        else{
            $this->delay = $period;
        }
    }

    public function slowDown(): void
    {
        $this->delay += ($this->minDelay * self::SLOWDOWN);
    }

    public function getCurrentDelay(): float
    {
        return $this->delay;
    }
}
