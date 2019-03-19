<?php

namespace App\Tests\Functional\Service\Apa;

use App\Entity\Edition;
use App\Entity\Lead;
use App\Entity\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers App\Service\Apa\ProductParser
 */
class ProductParserTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $asin               = '0674979850';

        $em                 = self::$container->get('doctrine')->getManager();
        $dbh                = $em->getConnection();
        $editionManager     = self::$container->get('test.App\Service\EditionManager');

        $editionManager->stubEditions([$asin]);

        $edition = $this->loadEdition('product-sample.xml');

        // validate the object type
        $this->assertInstanceOf(
            Edition::class,
            $edition
        );

        // validate similar edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            10,
            $count
        );

        // validate similar primary_browsenode_edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM primary_browsenode_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            8,
            $count
        );

        // validate similar browsenode_edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM browsenode_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            14,
            $count
        );

        // validate work count
        $count = $dbh->query('SELECT COUNT(*) as c FROM work')->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
        );
    }

    public function testRejectedNullFormat(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $dbh                = $em->getConnection();

        $edition = $this->loadEdition('product-sample-no-binding.xml');

        $this->assertTrue(
            $edition->getRejected()
        );

        // validate similar edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            0,
            $count
        );
    }

    public function testRejectedUnknownFormat(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $dbh                = $em->getConnection();

        $edition = $this->loadEdition('product-sample-weird-format.xml');

        $this->assertTrue(
            $edition->getRejected()
        );

        // validate similar edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            0,
            $count
        );
    }

    public function testBookAudioCD(): void
    {
        $edition = $this->loadEdition('product-sample-audio-cd.xml');

        $this->assertFalse(
            $edition->getRejected()
        );
    }

    public function testMusicAudioCD(): void
    {
        $edition = $this->loadEdition('product-sample-audio-cd-music.xml');

        $this->assertTrue(
            $edition->getRejected()
        );
    }

    public function testEroticaByNode(): void
    {
        $edition = $this->loadEdition('product-sample-erotica-by-node.xml');

        $this->assertTrue(
            $edition->getRejected()
        );
    }

    public function testEroticaByPublisher(): void
    {
        $edition = $this->loadEdition('product-sample-erotica-by-publisher.xml');

        $this->assertTrue(
            $edition->getRejected()
        );
    }

    public function testDumbPrice(): void
    {
        $edition = $this->loadEdition('product-sample-dump-price.xml');

        $this->assertEquals(
            588614.56,
            $edition->getListPrice()
        );
    }


    private function loadEdition(
        string $file
    ): ?Edition
    {
        return $this->loadEditionFromXML($file);
    }
}
