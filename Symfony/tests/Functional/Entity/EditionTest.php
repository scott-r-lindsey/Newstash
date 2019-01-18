<?php
declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\Edition;

/**
 * @covers App\Entity\Edition
 */
class EditionTest extends BaseTest
{

    protected $DBSetup = true;
    private $em;

    /**
     *
     */
    public function setUp()
    {
        parent::setup();
        $this->em = self::$container->get('doctrine')->getManager();
    }


    /**
     *
     */
    public function testCreateBrowseNode(): void
    {

        $timestamp = time();

        $publisher_scraped_at   = \DateTime::createFromFormat( 'U', (string)($timestamp-25) );
        $amzn_scraped_at        = \DateTime::createFromFormat( 'U', (string)($timestamp-20) );
        $amzn_updated_at        = \DateTime::createFromFormat( 'U', (string)($timestamp-15) );
        $publication_data       = \DateTime::createFromFormat( 'U', (string)($timestamp-10) );
        $release_data           = \DateTime::createFromFormat( 'U', (string)($timestamp-5) );

        $edition = $this->createEdition(
            'A123456789',
            '9781234567890',
            'The Title',
            'The Pub Title',
            'The Pub Subtitle',
            $publisher_scraped_at,
            $amzn_scraped_at,
            $amzn_updated_at,
            'http://somewhere.test/url',
            'Joe Author',
            ['some author', 'some other author'],
            'small cover',
            100,
            120,
            'medium cover',
            200,
            220,
            'large cover',
            300,
            320,
            1.25,
            1.05,
            'amzn publisher',
            1,
            'paperback',
            100,
            $publication_data,
            $release_data,
            'the description',
            'azn editorial review source',
            'azn editorial review',
            'azn alternatives',
            'azn edition',
            'azn manufacturer',
            'azn_brand'
        );

        $this->em->flush();
        $this->em->clear();

        // --------------------------------------------------------------------

        $new_edition = $this->em->getRepository(Edition::class)
            ->findOneByAsin($edition->getAsin());

        $this->assertEquals(
            [
                'A123456789',
                '9781234567890',
                'The Title',
                'The Pub Title',
                'The Pub Subtitle',
                $publisher_scraped_at,
                $amzn_scraped_at,
                $amzn_updated_at,
                'http://somewhere.test/url',
                'Joe Author',
                ['some author', 'some other author'],
                'small cover',
                100,
                120,
                'medium cover',
                200,
                220,
                'large cover',
                300,
                320,
                '1.25',
                '1.05',
                'amzn publisher',
                1,
                'paperback',
                100,
                $publication_data,
                $release_data,
                'the description',
                'azn editorial review source',
                'azn editorial review',
                'azn alternatives',
                'the title|some author',
                'azn edition',
                'azn manufacturer',
                'azn_brand'
            ],
            [
                $new_edition->getAsin(),
                $new_edition->getIsbn(),
                $new_edition->getTitle(),
                $new_edition->getPubTitle(),
                $new_edition->getPubSubTitle(),
                $new_edition->getPublisherScrapedAt(),
                $new_edition->getAmznScrapedAt(),
                $new_edition->getAmznUpdatedAt(),
                $new_edition->getUrl(),
                $new_edition->getAmznAuthorDisplay(),
                $new_edition->getAmznAuthorlist(),
                $new_edition->getAmznSmallCover(),
                $new_edition->getAmznSmallCoverX(),
                $new_edition->getAmznSmallCoverY(),
                $new_edition->getAmznMediumCover(),
                $new_edition->getAmznMediumCoverX(),
                $new_edition->getAmznMediumCoverY(),
                $new_edition->getAmznLargeCover(),
                $new_edition->getAmznLargeCoverX(),
                $new_edition->getAmznLargeCoverY(),
                $new_edition->getListPrice(),
                $new_edition->getAmznPrice(),
                $new_edition->getAmznPublisher(),
                $new_edition->getAmznSalesrank(),
                $new_edition->getAmznFormat(),
                $new_edition->getPages(),
                $new_edition->getPublicationDate(),
                $new_edition->getReleaseDate(),
                $new_edition->getDescription(),
                $new_edition->getAmznEditorialReviewSource(),
                $new_edition->getAmznEditorialReview(),
                $new_edition->getAmznAlternatives(),
                $new_edition->getSig(),
                $new_edition->getAmznEdition(),
                $new_edition->getAmznManufacturer(),
                $new_edition->getAmznBrand()
            ]
        );

        $this->assertEquals(
            'the-title-by-joe-author',
            $edition->getSlug()
        );


    }



    # -------------------------------------------------------------------------

    /**
     *
     */


    private function createEdition
    (
        string $asin,
        string $isbn,
        string $title,
        string $pub_title,
        string $pub_subtitle,
        \DateTime $publisher_scraped_at,
        \DateTime $amzn_scraped_at,
        \DateTime $amzn_updated_at,
        string $url,
        string $amzn_authordisplay,
        array $amzn_authorlist,
        string $amzn_small_cover,
        int $amzn_small_cover_x,
        int $amzn_small_cover_y,
        string $amzn_medium_cover,
        int $amzn_medium_cover_x,
        int $amzn_medium_cover_y,
        string $amzn_large_cover,
        int $amzn_large_cover_x,
        int $amzn_large_cover_y,
        float $list_price,
        float $amzn_price,
        string $amzn_publisher,
        int $amzn_salesrank,
        string $amzn_format,
        int $pages,
        \DateTime $publication_date,
        \DateTime $release_date,
        string $description,
        string $amzn_editorial_review_source,
        string $amzn_editorial_review,
        string $amzn_alternatives,
        string $amzn_edition,
        string $amzn_manufacturer,
        string $amzn_brand
    ): Edition {

        $edition = new Edition();

        $edition
            ->setAsin($asin)
            ->setIsbn($isbn)
            ->setTitle($title)
            ->setPubTitle($pub_title)
            ->setPubSubTitle($pub_subtitle)
            ->setPublisherScrapedAt($publisher_scraped_at)
            ->setAmznScrapedAt($amzn_scraped_at)
            ->setAmznUpdatedAt($amzn_updated_at)
            ->setUrl($url)
            ->setAmznAuthorDisplay($amzn_authordisplay)
            ->setAmznAuthorlist($amzn_authorlist)
            ->setAmznSmallCover($amzn_small_cover)
            ->setAmznSmallCoverX($amzn_small_cover_x)
            ->setAmznSmallCoverY($amzn_small_cover_y)
            ->setAmznMediumCover($amzn_medium_cover)
            ->setAmznMediumCoverX($amzn_medium_cover_x)
            ->setAmznMediumCoverY($amzn_medium_cover_y)
            ->setAmznLargeCover($amzn_large_cover)
            ->setAmznLargeCoverX($amzn_large_cover_x)
            ->setAmznLargeCoverY($amzn_large_cover_y)
            ->setListPrice($list_price)
            ->setAmznPrice($amzn_price)
            ->setAmznPublisher($amzn_publisher)
            ->setAmznSalesrank($amzn_salesrank)
            ->setAmznFormat($amzn_format)
            ->setPages($pages)
            ->setPublicationDate($publication_date)
            ->setReleaseDate($release_date)
            ->setDescription($description)
            ->setAmznEditorialReviewSource($amzn_editorial_review_source)
            ->setAmznEditorialReview($amzn_editorial_review)
            ->setAmznAlternatives($amzn_alternatives)
            ->setAmznEdition($amzn_edition)
            ->setAmznManufacturer($amzn_manufacturer)
            ->setAmznBrand($amzn_brand);

        $this->em->persist($edition);

        return $edition;
    }
}
