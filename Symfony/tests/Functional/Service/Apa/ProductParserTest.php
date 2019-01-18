<?php

namespace App\Tests\Functional\Service\Apa\ProductParser;

use App\Entity\Edition;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ProductParserTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {
        $edition = $this->loadEdition('product-sample.xml');

        $this->assertInstanceOf(
            Edition::class,
            $edition
        );
    }

    public function testRejectedNullFormat(): void
    {
        $edition = $this->loadEdition('product-sample-no-binding.xml');

        $this->assertTrue(
            $edition->getRejected()
        );
    }

    public function testRejectedUnknownFormat(): void
    {
        $edition = $this->loadEdition('product-sample-weird-format.xml');

        $this->assertTrue(
            $edition->getRejected()
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

    private function loadEdition(string $file): ?Edition
    {
        $root   = self::$container->get('kernel')->getProjectDir();
        $xml    = file_get_contents("$root/tests/Fixture/$file");

        $sxe = new SimpleXMLElement($xml);

        $productParser = self::$container->get('test.App\Service\Apa\ProductParser');

        return $productParser->ingest($sxe->Items->Item[0]);
    }
}
