<?php
declare(strict_types=1);

namespace App\Service\Data;

use App\Entity\Edition;
use App\Service\FormatCache;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class FrontFinder
{

    private $logger;
    private $em;
    private $formatCache;

    private $formats    = false;
    private $paper      = [];
    private $e          = [];
    private $audio      = [];


    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        FormatCache $formatCache
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->formatCache          = $formatCache;
    }

    public function isPaper(int $id): bool
    {
        $this->getFormats();
        return in_array($id, $this->paper);
    }

    public function isE(int $id): bool
    {
        $this->getFormats();
        return in_array($id, $this->e);
    }

    public function isAudio(int $id): bool
    {
        $this->getFormats();
        return in_array($id, $this->audio);
    }

    public function getFront(array $editions): Edition
    {
        $edition_data   = [];
        $by_key         = [];

        foreach ($editions as $e){
            $edition_data[] = [
                'asin'          => $e->getAsin(),
                'format_id'     => $e->getFormat()->getId(),
                'has_cover'     => (boolean)$e->getAmznLargeCover(),
                'active'        => $e->getActive(),
                'releaseDate'   => $e->getReleaseDate()
            ];
            $by_key[$e->getAsin()] = $e;
        }

        $front = $this->getFrontLogic($edition_data);

        if (isset($by_key[$front['asin']])){
            return $by_key[$front['asin']];
        }

        throw new \Exception('failed to find front book?');
    }

    public function getFrontLogic($edition_data)
    {
        $this->getFormats();

        // frontmost title, order by:
        // most recent paper edition w/ cover
        // most recent ebook w/cover
        // most recent audio w/cover
        // most recent paper edition w/out cover
        // most recent ebook w/out cover
        // most recent audio w/out cover

        $paperWcover        = [];
        $eWcover            = [];
        $audioWcover        = [];
        $paperWOcover       = [];
        $eWOcover           = [];
        $audioWOcover       = [];

        usort($edition_data, array(&$this, "cmp"));

        foreach ($edition_data as $ed){
            if (!$ed['active']){
                continue;
            }

            if ($ed['has_cover']){
                if ($this->isPaper((int)$ed['format_id'])){          $paperWcover[] = $ed; }
                else if ($this->isE((int)$ed['format_id'])){         $eWcover[] = $ed; }
                else if ($this->isAudio((int)$ed['format_id'])){     $audioWcover[] = $ed; }
            }
            else{
                if ($this->isPaper((int)$ed['format_id'])){          $paperWOcover[] = $ed; }
                else if ($this->isE((int)$ed['format_id'])){         $eWOcover[] = $ed; }
                else if ($this->isAudio((int)$ed['format_id'])){     $audioWOcover[] = $ed; }
            }
        }

        $all = array_merge(
            $paperWcover,
            $eWcover,
            $audioWcover,
            $paperWOcover,
            $eWOcover,
            $audioWOcover
        );

        if (count($all)){
            return $all[0];
        }
        return false;
    }

    // ------------------------------------------------------------------------

    static function cmp($b, $a): int
    {
        if ($a['releaseDate'] == $b['releaseDate']){
            return 0;
        }
        return $a['releaseDate'] < $b['releaseDate'] ? -1 : 1;
    }

    // ------------------------------------------------------------------------

    private function getFormats(): void
    {
        if ($this->formats){
            return;
        }

        foreach ($this->formatCache->getFormatsByName() as $f) {

            if ($f->getPaper()) {
                $this->paper[] = $f->getId();
            }
            if ($f->getE()) {
                $this->e[] = $f->getId();
            }
            if ($f->getAudio()) {
                $this->audio[] = $f->getId();
            }
        }
        $this->formats = true;
    }
}
