<?php
declare(strict_types=1);

namespace App\Service\Cache;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FormatCache
{

    private $formats            = [];
    private $extendedFormats    = [];

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
    }

    public function getFormatsByName(): array
    {
        $this->validate();

        if (0 === count($this->formats)) {
            $this->logger->info("Loading formats");

            $dql = '
                SELECT f
                FROM
                    App\Entity\Format f';

            $query      = $this->em->createQuery($dql);

            $formats    = $query->getResult();

            foreach ($formats as $f) {
                $this->formats[$f->getDescription()] = $f;
            }
        }

        return $this->formats;
    }

    public function getExtendedFormatsByName(): array
    {
        $this->validate();

        if (0 === count($this->extendedFormats)) {
            $this->logger->info("Loading extended formats");

            $formats = $this->getFormatsByName();

            foreach ($formats as $f) {
                $this->extendedFormats[$f->getDescription()] = $f;
            }

            $this->extendedFormats['Mass Market Paperback']     = $this->formats['Paperback'];
            $this->extendedFormats['Kindle Edition']            = $this->formats['Kindle eBook'];
        }

        return $this->extendedFormats;
    }

    private function validate(): void
    {
        if (count($this->formats)) {
            if (!$this->em->contains($this->formats['Paperback'])){
                $this->flush();
            }
        }
    }

    public function flush(): void
    {
        $this->formats            = [];
        $this->extendedFormats    = [];
    }
}
