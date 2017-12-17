<?php

namespace App\Service;

use Psr\Log\LoggerInterface;

class BookParser
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getHappyMessage()
    {
        $this->logger->info('This logs a thing');
    }
}
