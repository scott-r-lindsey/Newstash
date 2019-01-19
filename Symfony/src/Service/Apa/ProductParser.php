<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Entity\Edition;
use App\Service\Apa\ProductRejector;
use App\Service\FormatCache;
use App\Service\IsbnConverter;
use App\Service\PubFixer;
use App\Service\EditionManager;
use App\Service\LeadManager;
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
    private $formatCache;
    private $editionManager;
    private $leadManager;

    private $formats            = [];
    private $formats_noprice    = [];

    const SALES_RANK_MAX    = 2147483647;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        IsbnConverter $isbnConverter,
        PubFixer $pubfixer,
        ProductRejector $rejector,
        FormatCache $formatCache,
        EditionManager $editionManager,
        LeadManager $leadManager
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->isbnConverter        = $isbnConverter;
        $this->pubfixer             = $pubfixer;
        $this->rejector             = $rejector;
        $this->formatCache          = $formatCache;
        $this->editionManager       = $editionManager;
        $this->leadManager          = $leadManager;
    }

    public function ingest(
        SimpleXMLElement $sxe,
        Edition $edition = null
    ): ?Edition
    {

        $this->initHtmlPurifier();

        if (null == $edition) {
            $edition = new Edition();
            $edition->setAsin((string)$sxe->ASIN);
            $this->em->persist($edition);
        }

        $edition    = $this->parseMetaData($sxe, $edition);
        $asin       = $edition->getAsin();

        $valid = $this->rejector->evaluate($sxe, $edition);

        $this->leadManager->leadFollowed($asin, $edition->getRejected(), $edition->getAmznFormat());





        $this->em->flush();

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
        }
        // --------------------------------------------------------------------
        // similar products

        if (($sxe->SimilarProducts) && ($sxe->SimilarProducts->SimilarProduct)){

            foreach ($sxe->SimilarProducts->SimilarProduct as $a){
                $foundAsins[]    = (string)$a->ASIN;
            }
        }

        if (count($foundAsins)) {
            $this->leadManager->newLeads($foundAsins);
        }





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
