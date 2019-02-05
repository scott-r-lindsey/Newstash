<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Statement;
use Psr\Log\LoggerInterface;

/**
 *
 */
class DeadLockRetryManager{

    private $logger;
    const MAX_RETRY = 10;

    public function __construct(
        LoggerInterface $logger
    ){
        $this->logger       = $logger;
    }

    public function exec(
        Statement $sth,
        array $bind
    ): void
    {

        $fail = 0;
        while (true) {

            try {
                $sth->execute($bind);
                break;

            } catch (DeadlockException $e) {

                $fail++;

                if (10 === $fail) {
                    throw $e;
                }

                $this->logger->info(
                    "Caught deadlock exception retrying ($fail)");
            }
        }
    }
}
