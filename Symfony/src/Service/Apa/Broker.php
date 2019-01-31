<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Service\Apa;
use App\Service\Apa\ProductApi;
use App\Service\Apa\ProductParser;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Closure;

class Broker
{

    private $logger;
    private $mm;
    private $productApi;

    private $queue      = [];


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
        $asins = [];
        foreach ($this->queue as $q) {
            $asins[] = $q['asin'];
        }
        $query = implode(',', $asins);

        $sxe = $this->productApi->ItemLookup(
            $asins,
            Apa::STANDARD_RESPONSE_TYPES
        );

        foreach ($sxe->Items->Item as $item){
            $this->runIngest($item);
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

    private function runIngest($sxe)
    {
        // forking may be implemented here if required
        $this->logger->info("Ingesting http://amzn.com/" . (string)$sxe->ASIN);
        $this->productParser->ingest($sxe);
        $this->logger->info("Finished ingesting " . (string)$sxe->ASIN);
    }

}
