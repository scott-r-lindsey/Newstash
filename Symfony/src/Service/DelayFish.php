<?php
declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

/**
 * 
 */

class DelayFish{

    private $frequency;
    private $delay;
    private $last = 0;

    public function __construct(
        LoggerInterface $logger,
        float $delay
    ){
        $this->frequency = $delay;
        $this->logger = $logger;
    }

    public function delay(): void
    {
        $now = microtime(true);

        if (($this->last + $this->frequency) > $now) {

            $nap_secs = $this->frequency - ($now - $this->last);

            $this->logger->info("Sleeping for $nap_secs seconds");

            usleep ((int)($nap_secs * 1000000));
        }

        $this->last = microtime(true);
    }
}
