<?php
declare(strict_types=1);

namespace App\Service\Apa;

use App\Entity\Edition;
use App\Service\IsbnConverter;
use App\Service\PubFixer;
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
    private $formats            = [];
    private $formats_noprice    = [];

    const SALES_RANK_MAX = 2147483647;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        IsbnConverter $isbnConverter,
        PubFixer $pubfixer
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->isbnConverter        = $isbnConverter;
        $this->pubfixer             = $pubfixer;
    }

    public function ingest(
        SimpleXMLElement $sxe,
        Edition $edition = null
    ): ?Edition
    {

        $this->loadFormats();
        $this->initHtmlPurifier();

        if (null == $edition) {
            $edition = new Edition();
            $edition->setAsin((string)$sxe->ASIN);
            $this->em->persist($edition);
        }

        $edition = $this->parseMetaData($sxe, $edition);



        // fixes and rejections here









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
            ->setIsbn(              $this->isbnFromSxe($sxe))
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

        if (isset($this->formats[(string)$sxe->ItemAttributes->Binding])) {
            if ($format = $this->formats[(string)$sxe->ItemAttributes->Binding]){
                $edition->setFormat($format);
            }
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

        return $edition;;
    }

    private function isbnFromSxe(SimpleXMLElement $sxe): ?string
    {
        $ic = $this->isbnConverter;
        $isbn = null;

        if (isset($sxe->ItemAttributes->EAN)){
            $isbn = (string)$sxe->ItemAttributes->EAN;
        }
        else if (isset($sxe->ItemAttributes->ISBN)){
            $isbn = (string)$sxe->ItemAttributes->ISBN;
            if (13 != strlen($isbn)){
                $isbn = $ic->isbn10to13($isbn);
            }
        }
        else if (isset($sxe->ItemAttributes->EISBN)){
            $isbn = (string)$sxe->ItemAttributes->EISBN;
            if (13 != strlen($isbn)){
                $isbn = $ic->isbn10to13($isbn);
            }
        }
        return $isbn;
    }

    private function loadFormats(): void
    {

        if (0 === count($this->formats)) {

            $dql = '
                SELECT f
                FROM
                    App\Entity\Format f';

            $query      = $this->em->createQuery($dql);
            $formats    = $query->getResult();

            foreach ($formats as $f) {
                $this->formats[$f->getDescription()] = $f;
                $this->formats_noprice[$f->getDescription()] = $f->getNoprice();
            }

            $this->formats['Mass Market Paperback']             = $this->formats['Paperback'];
            $this->formats['Kindle Edition']                    = $this->formats['Kindle eBook'];

            $this->formats_noprice['Mass Market Paperback']     = $this->formats_noprice['Paperback'];
            $this->formats_noprice['Kindle Edition']            = $this->formats_noprice['Kindle eBook'];
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
