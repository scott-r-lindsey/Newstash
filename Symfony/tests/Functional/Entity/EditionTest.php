<?php
declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\BrowseNode;

/**
 * @covers App\Entity\Edition
 */
class EditionTest extends BaseTest
{

    /**
     *
     */
    public function setUp()
    {
        parent::setup();
        $this->em = $this->container->get('doctrine')->getManager();
    }


    /**
     *
     */
    public function testCreateBrowseNode(): void
    {

/*
        $edition = $this->createEdition(

        );
*/

        $this->em->flush();
        $this->em->clear();

    }



    # -------------------------------------------------------------------------

    /**
     *
     */


    private function createEdition
    (
        string $asin,
        int $isbn,
        string $title,
        string $pub_title,
        string $pub_subtitle,
        \DateTime $publisher_scraped_at,
        \DateTime $amzn_scraped_at,
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
        string $sig,
        string $amzn_edition,
        string $amzn_manufacturer,
        string $amzn_brand
    ): Editioni {


    }
}
