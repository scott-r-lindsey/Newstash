<?php

namespace App\Tests\Functional\Service\Apa\ProductParser;

use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class FakeTest extends BaseTest
{
    protected $DBSetup = true;

    public function testService(): void
    {

        $root   = self::$container->get('kernel')->getProjectDir();
        $xml    = file_get_contents("$root/tests/Fixture/product-sample.xml");

        $sxe = new SimpleXMLElement($xml);

        $productParser = self::$container->get('test.App\Service\Apa\ProductParser');

        $productParser->ingest($sxe->Items->Item[0]);





        $this->assertTrue(true);
    }

}
