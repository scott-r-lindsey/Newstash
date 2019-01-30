<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Entity\Edition;
use App\Service\Apa\ProductRejector;
use App\Service\FormatCache;
use App\Service\BrowseNodeCache;
use App\Service\IsbnConverter;
use App\Service\PubFixer;
use App\Service\EditionManager;
use App\Service\Data\WorkGroomer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use HtmlPurifier;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class ProductParser
{
    private $logger;
    private $em;
    private $isbnConverter;
    private $purifier;
    private $pubfixer;
    private $rejector;
    private $browseNodeCache;
    private $formatCache;
    private $editionManager;
    private $workGroomer;

    private $formats            = [];
    private $formats_noprice    = [];

    const SALES_RANK_MAX    = 2147483647;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        IsbnConverter $isbnConverter,
        PubFixer $pubfixer,
        ProductRejector $rejector,
        BrowseNodeCache $browseNodeCache,
        FormatCache $formatCache,
        EditionManager $editionManager,
        WorkGroomer $workGroomer
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->isbnConverter        = $isbnConverter;
        $this->pubfixer             = $pubfixer;
        $this->rejector             = $rejector;
        $this->browseNodeCache      = $browseNodeCache;
        $this->formatCache          = $formatCache;
        $this->editionManager       = $editionManager;
        $this->workGroomer          = $workGroomer;
    }

    public function ingest(
        SimpleXMLElement $sxe
    ): ?Edition
    {

        $this->initHtmlPurifier();

        $asin = (string)$sxe->ASIN;
        $edition = $this->em->getRepository(Edition::class)
            ->findOneByAsin($asin);

        if (null == $edition) {
            $this->editionManager->stubEdition($asin);
            $edition = $this->em->getRepository(Edition::class)
                ->findOneByAsin($asin);
        }

        $edition    = $this->parseMetaData($sxe, $edition);
        $asin       = $edition->getAsin();

        $valid = $this->rejector->evaluate($sxe, $edition);

        $this->em->flush();

        $this->workGroomer->workGroom($edition);

        return $edition;
    }

    private function parseMetaData(
        SimpleXMLElement $sxe,
        Edition $edition
    ): ?Edition
    {
        // --------------------------------------------------------------------
        // basic data

        $edition
            ->setIsbn(              $this->isbnConverter->isbnFromSxe($sxe))
            ->setTitle(             (string)$sxe->ItemAttributes->Title)
            ->setPages(             (int)$sxe->ItemAttributes->NumberOfPages)
            ->setListPrice(         ((int)$sxe->ItemAttributes->ListPrice->Amount) /100)
            ->setAmznUpdatedAt(     new DateTime())
            ->setAmznSmallCover(    (string)$sxe->SmallImage->URL)
            ->setAmznSmallCoverX(   (int)$sxe->SmallImage->Width)
            ->setAmznSmallCoverY(   (int)$sxe->SmallImage->Height)
            ->setAmznMediumCover(   (string)$sxe->MediumImage->URL)
            ->setAmznMediumCoverX(  (int)$sxe->MediumImage->Width)
            ->setAmznMediumCoverY(  (int)$sxe->MediumImage->Height)
            ->setAmznLargeCover(    (string)$sxe->LargeImage->URL)
            ->setAmznLargeCoverX(   (int)$sxe->LargeImage->Width)
            ->setAmznLargeCoverY(   (int)$sxe->LargeImage->Height)
            ->setAmznFormat(        (string)$sxe->ItemAttributes->Binding)
            ->setAmznEdition(       (string)$sxe->ItemAttributes->Edition)
            ->setAmznManufacturer(  (string)$sxe->ItemAttributes->Manufacturer)
            ->setAmznBrand(         (string)$sxe->ItemAttributes->Brand)
        ;

        // --------------------------------------------------------------------
        // amazon publisher

        $amzn_publisher = $this->pubfixer->fix( (string)$sxe->ItemAttributes->Publisher);
        $edition ->setAmznPublisher($amzn_publisher);

        // --------------------------------------------------------------------
        // author(s)

        $authors = [];
        foreach ($sxe->ItemAttributes->Author as $author){
            $authors[] = (string)$author;
        }

        $author_display = '';
        if (1 == count($authors)){
            $author_display = $authors[0];
        }
        else if (2 == count($authors)){
            $author_display = $authors[0] . ' and ' . $authors[1];
        }
        else{
            $end = array_pop($authors);
            $author_display = implode(', ', $authors);
            $author_display .= ' and ' . $end;
        }

        $edition->setAmznAuthorDisplay($author_display)
            ->setAmznAuthorList(    $authors)
        ;

        // --------------------------------------------------------------------
        // sales rank

        $salesrank = (string)$sxe->SalesRank;
        if (( '0' == $salesrank ) || ('' == $salesrank )){
            $salesrank = self::SALES_RANK_MAX;
        }
        $edition->setAmznSalesrank((int)$salesrank);

        // --------------------------------------------------------------------
        // amazon price

        // FIXME this should be looked over some more
        if (($sxe->Offers) && ($sxe->Offers->Offer)){
            $edition->setAmznPrice((int)$sxe->Offers->Offer->OfferListing->Price->Amount /100);
        }

        // --------------------------------------------------------------------
        // editorial reviews

        if (($sxe->EditorialReviews) && ($sxe->EditorialReviews->EditorialReview)){
            foreach ($sxe->EditorialReviews->EditorialReview as $er){
                if ('Amazon.com Review' == (string)$er->Source){

                    $edition->setAmznEditorialReviewSource('Amazon.com Review')
                        ->setAmznEditorialReview((string)$er->Content);
                }
                if ('Product Description' == (string)$er->Source){

                    $edition->setDescription($this->purifier->purify((string)$er->Content));
                }
            }
        }

        // --------------------------------------------------------------------
        // pub and release dates

        if ($pubDate = (string)$sxe->ItemAttributes->PublicationDate){
            $edition->setPublicationDate(new DateTime($pubDate));
        }
        if ($releaseDate = (string)$sxe->ItemAttributes->ReleaseDate){
            $edition->setReleaseDate(new DateTime($releaseDate));
        }

        // --------------------------------------------------------------------
        // format

        $formats = $this->formatCache->getExtendedFormatsByName();

        if (isset($formats[(string)$sxe->ItemAttributes->Binding])) {
            if ($format = $formats[(string)$sxe->ItemAttributes->Binding]){
                $edition->setFormat($format);
            }
        }

        // --------------------------------------------------------------------
        // alternative versions

        $foundAsins = [];
        if (isset($sxe->AlternateVersions)){
            $foundAsins = [];

            foreach ($sxe->AlternateVersions->AlternateVersion as $a){
                $foundAsins[]    = (string)$a->ASIN;
            }

            $edition->setAmznAlternatives(implode(',', $foundAsins));
            $this->editionManager->stubEditions(($foundAsins));
        }
        // --------------------------------------------------------------------
        // similar products

        $foundAsins = [];
        if (($sxe->SimilarProducts) && ($sxe->SimilarProducts->SimilarProduct)){

            foreach ($sxe->SimilarProducts->SimilarProduct as $a){
                $foundAsins[]    = (string)$a->ASIN;
            }
        }

        if (count($foundAsins)) {
            $this->editionManager->stubEditions(($foundAsins));
            $this->editionManager->similarUpdate(
                $edition->getAsin(),
                $foundAsins
            );
        }

        // --------------------------------------------------------------------
        // browse nodes

        $this->updateBrowseNodes($edition->getAsin(), $sxe);


/*
        $edition
            ->setPubTitle($pub_title)
            ->setPubSubTitle($pub_subtitle)
            ->setPublisherScrapedAt($publisher_scraped_at)
            ->setAmznScrapedAt($amzn_scraped_at)
            ->setUrl($url)
            ->setAmznPrice($amzn_price)
            ->setAmznAlternatives($amzn_alternatives)
*/

        return $edition;
    }


    /**
     * FIXME
     * this implentation is fast for initial load, but writes for no
     * reason on update :-/
     **/
    private function updateBrowseNodes(
        string $asin,
        SimpleXMLElement $sxe
    ): void
    {

        $dbh = $this->em->getConnection();

        // --------------------------------------------------------------------
        // clear out existant

        $sql = '
            DELETE FROM browsenode_edition
            WHERE edition_asin = ?';

        $sth = $dbh->prepare($sql);
        $sth->execute([$asin]);

        $sql = '
            DELETE FROM primary_browsenode_edition
            WHERE edition_asin = ?';

        $sth = $dbh->prepare($sql);
        $sth->execute([$asin]);

        // --------------------------------------------------------------------
        // insert new associations

        $validNodeIds       = $this->browseNodeCache->getBrowseNodesIds();
        $node_ids           = [];
        $primary_node_ids   = [];

        // find nodes and all parents
        if (($sxe->BrowseNodes) and ($sxe->BrowseNodes->BrowseNode)){
            foreach ($sxe->BrowseNodes->BrowseNode as $bn){
                $primary_node_ids[] = (string)$bn->BrowseNodeId;
                $node_ids[(string)$bn->BrowseNodeId] = true;
                $b = $bn;
                while (($b->Ancestors) and ($a = $b->Ancestors)){
                    $b = $a->BrowseNode;
                    if ($b->IsCategoryRoot){
                        break;
                    }
                    $node_ids[(string)$b->BrowseNodeId] = true;
                }
            }
        }

        // We filter against $validNodeIds because we are not
        // taking all the nodes.  Mostly, skipping kindle categories.

        if (count($node_ids)){
            $sql = '
                INSERT INTO browsenode_edition
                    (edition_asin, browsenode_id)
                VALUES
                    (?, ?)';

            $sth = $dbh->prepare($sql);
            foreach (array_keys($node_ids) as $node_id){
                if (!isset($validNodeIds[$node_id])){
                    continue;
                }
                $sth->execute([$asin, $node_id]);
            }
        }
        if (count($primary_node_ids)){
            $sql = '
                INSERT INTO primary_browsenode_edition
                    (edition_asin, browsenode_id)
                VALUES
                    (?, ?)';

            $sth = $dbh->prepare($sql);
            foreach ($primary_node_ids as $node_id){
                if (!isset($validNodeIds[$node_id])){
                    continue;
                }
                $sth->execute([$asin, $node_id]);
            }
        }
    }

    private function initHtmlPurifier(): void
    {
        if (!$this->purifier) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('HTML.AllowedElements', array(
                'b', 'br', 'em', 'font', 'h1', 'h2', 'h3', 'h4',
                'h5', 'h6', 'hr', 'i', 'li', 'ol', 'p', 'pre',
                's', 'strike', 'strong', 'sub', 'sup', 'u', 'ul'));

            $config->set('HTML.AllowedAttributes', array());
            $this->purifier = new \HTMLPurifier($config);
        }
    }
}
