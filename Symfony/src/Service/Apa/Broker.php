<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Service\Apa;
use App\Service\Apa\ProductApi;
use App\Service\Apa\ProductParser;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Closure;
use Ko\Process;
use Ko\ProcessManager;

class Broker
{

    private $logger;
    private $mm;
    private $productApi;
    private $processManager;

    private $fork       = false;
    private $queue      = [];
    private $maxProc    = 10;

    // --------------------------------------------------------------------------------------
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ProductApi $productApi,
        ProductParser $productParser
    ){
        $this->em               = $em;
        $this->logger           = $logger;
        $this->productApi       = $productApi;
        $this->productParser    = $productParser;

        $this->processManager   = new ProcessManager();
    }

    public function enqueue(
        string $asin
    ): void
    {

        $this->queue[] = compact('asin');

        if (Apa::MAX_REQUEST_SIZE === count($this->queue)) {
            $this->process();
        }
    }

    public function process(): void
    {

        $this->em->clear();
        gc_collect_cycles();

        $asins = [];
        foreach ($this->queue as $q) {
            $asins[] = $q['asin'];
        }
        $query = implode(',', $asins);

        $sxe = $this->productApi->ItemLookup(
            $asins,
            Apa::STANDARD_RESPONSE_TYPES
        );

        if ($this->fork) {

            $this->processManager->dispatch();

            while ($this->processManager->count() >= $this->maxProc) {
                $this->processManager->dispatch();
                usleep(250000); // quarter second
            }

            $this->em->getConnection()->close();

            $this->processManager->fork(
                function(Process $p) use ($sxe) {
                    $this->runIngest($sxe);
                    exit;
            });
        }
        else{
            $this->runIngest($sxe);
        }

        $this->clear();
    }

    public function getQueueCount(): int
    {
        return count($this->queue);
    }

    public function clear(): void
    {
        $this->queue = [];
    }

    public function setFork(bool $fork): void
    {
        $this->fork = $fork;
    }

    public function wait(): void
    {
        $this->processManager->wait();
    }


    private function runIngest($sxe)
    {
        foreach ($sxe->Items->Item as $item){
            $this->logger->info("Ingesting http://amzn.com/" . (string)$item->ASIN);
            $this->productParser->ingest($item);
            $this->logger->info("Finished ingesting " . (string)$item->ASIN);
        }
    }
}
