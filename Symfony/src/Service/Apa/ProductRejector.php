<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Entity\Edition;
use App\Service\FormatCache;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class ProductRejector
{
    private $logger;
    private $formatCache;

    const SALES_RANK_MAX    = 2147483647;
    const NODES_BOOK        = [283155, 154606011];

    const BLOCKED_NODES     = [
        2029,           // Books - Subjects - Arts & Photography - Photography & Video - Erotic Photography
        5824913011,     // Books - Subjects - Arts & Photography - History & Criticism - Themes - Erotica
        16244381,       // Books - Subjects - Arts & Photography - Other Media - Erotic
        2309013011,     // Kindle Store - Categories - Kindle eBooks - Arts & Photography - Art - Erotica
        2243074011,     // Kindle Store - Categories - Kindle eBooks - Arts & Photography - Photography - Erotica
        7406072011,     // Comics & Graphic Novels - Manga - Erotica
        1091181,        // Literature & Fiction - Erotica
    ];

    const BLOCKED_PUBLISHERS    = [
        '/^Sheer City.*/',
        '/Naked Women Being Naked/',
        '/Adult Entertainment Productions/',
        '/NudePics/',
        '/Raw Amateur Models/',
        '/Brandon Carlscon Sex Photo Ebooks/',
        '/Project-H LLC/',
        '/Big Boobs Photo Lover/',
        '/DivineBreasts.com/',
        '/ErotiPics/',
    ];


    public function __construct(
        LoggerInterface $logger,
        FormatCache $formatCache
    )
    {
        $this->logger               = $logger;
        $this->formatCache          = $formatCache;
    }

    public function evaluate(
        SimpleXMLElement $sxe,
        Edition $edition
    ): bool
    {

        $extendedFormats = $this->formatCache->getExtendedFormatsByName();

        // out of scope formats
        if (null == $edition->getAmznFormat()) {
            return $this->exitRejected("Skipped null format", $edition);
        }
        else{
            if (! isset($extendedFormats[$af = $edition->getAmznFormat()] )){
                return $this->exitRejected("Skipped format $af)", $edition);
            }
        }

        // cd categorized as a book?
        if ('Audio CD' == $edition->getAmznFormat()) {

            $book = false;

            if (($sxe->BrowseNodes) and ($sxe->BrowseNodes->BrowseNode)){
                foreach ($sxe->BrowseNodes->BrowseNode as $BrowseNode){
                    if ($this->validTopNode($BrowseNode)){
                        $book = true;
                        continue;
                    }
                }
            }
            if (!$book){
                return $this->exitRejected("Skipping non-book CD", $edition);
            }
        }

        // filter out erotica
        $erotic = false;
        if (($sxe->BrowseNodes) and ($sxe->BrowseNodes->BrowseNode)) {
            foreach ($sxe->BrowseNodes->BrowseNode as $BrowseNode) {
                if (in_array($BrowseNode->BrowseNodeId, self::BLOCKED_NODES)){
                    return $this->exitRejected("Skipped erotica by node", $edition);
                }
            }
        }
        if ($pub = (string)$sxe->ItemAttributes->Manufacturer){
            foreach (self::BLOCKED_PUBLISHERS as $regex){
                if (preg_match($regex, $pub)){
                    return $this->exitRejected("Skipped erotica by publisher regex", $edition);
                }
            }
        }

        return true;
    }

    private function validTopNode(SimpleXMLElement $BrowseNode): bool
    {

        foreach (self::NODES_BOOK as $needle) {
            if ($needle == $BrowseNode->BrowseNodeId) {
                return true;
            }
        }
        if ($BrowseNode->Ancestors) {
            foreach ($BrowseNode->Ancestors as $Ancestor) {
                if ($this->validTopNode($Ancestor->BrowseNode)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function exitRejected(
        string $reason,
        Edition $edition
    ): bool
    {
        $asin = $edition->getAsin();

        $this->logger->info($reason . " (http://amzn.com/$asin)");
        //print ($reason . " (http://amzn.com/$asin)");
        $edition->setRejected(true);

        return false;
    }
}
