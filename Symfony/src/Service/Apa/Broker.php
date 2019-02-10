<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Service\Apa;
use App\Service\Apa\ProductApi;
use App\Service\Apa\ProductParser;
use App\Service\EditionManager;
use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Ko\Process;
use Ko\ProcessManager;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class Broker
{

    private $em;
    private $logger;
    private $productApi;
    private $productParser;
    private $editionManager;

    private $processManager;

    private $workGroom  = true;
    private $fork       = false;
    private $queue      = [];
    private $maxProc    = 5;
    private $procCount  = 0;

    const CLEAR_PROC    = 10;

    // --------------------------------------------------------------------------------------
    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        ProductApi $productApi,
        ProductParser $productParser,
        EditionManager $editionManager
    ){
        $this->em               = $em;
        $this->logger           = $logger;
        $this->productApi       = $productApi;
        $this->productParser    = $productParser;
        $this->editionManager   = $editionManager;

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

    public function setWorkGroom(bool $groom): void
    {
        $this->workGroom = $groom;
    }

    public function process(): void
    {
        if (count($this->queue)) {

            // clear the em after CLEAR_PROD cycles
            if (self::CLEAR_PROC === $this->procCount) {
                $this->em->clear();
                gc_collect_cycles();
                $this->procCount = 0;
            }

            $this->procCount++;

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
                    function(Process $p) use ($sxe, $query, $asins) {

                        $p->setProcessTitle("APA Ingest $query");

                        $this->runIngest($sxe, $asins);
                        exit;
                });
            }
            else{
                $this->runIngest($sxe, $asins);
            }

            $this->clear();
        }
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


    private function runIngest(SimpleXmlElement $sxe, array $asins)
    {
        $processed = [];
        foreach ($sxe->Items->Item as $item){
            $this->logger->info("Ingesting http://amzn.com/" . (string)$item->ASIN);

            $edition        = $this->productParser->ingest($item, $this->workGroom);
            if ($edition) {
                $processed[]    = $edition->getAsin();
            }

            $this->logger->debug("Finished ingesting http://amzn.com/" . (string)$item->ASIN);
        }

        $missing = array_diff($asins, $processed);
        foreach ($missing as $m) {
            $this->editionManager->markRejected($m);
        }
    }
}
