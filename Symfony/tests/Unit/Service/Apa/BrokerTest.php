<?php

namespace App\Tests\Unit\Service;

use App\Service\Apa;
use App\Service\Apa\Broker;
use App\Service\Apa\ProductApi;
use App\Service\Apa\ProductParser;
use App\Tests\Lib\BaseTest;
use SimpleXMLElement;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Log\Logger;

class BrokerTest extends BaseTest
{

    private $mockLogger;
    private $mockEntityManager;
    private $mockProductApi;
    private $mockProductParser;

    public function testCount(): void
    {
        $apaBroker = $this->buildBroker();

        $apaBroker->enqueue('A123456789', function() { print "sup\n"; });

        $this->assertEquals(
            1,
            $apaBroker->getQueueCount()
        );
    }

    public function testClear(): void
    {
        $apaBroker = $this->buildBroker();

        $apaBroker->enqueue('A123456789', function() { print "sup\n"; });
        $apaBroker->clear();

        $this->assertEquals(
            0,
            $apaBroker->getQueueCount()
        );
    }

    public function testProcess(): void
    {
        $apaBroker = $this->buildBroker();

        $sxe = $this->readXML('product-sample.xml');

        $apaBroker->enqueue('A123456789', function() { print "sup\n"; });

        $this->mockProductApi->expects($this->once())
            ->method('ItemLookup')
            ->with(['A123456789'], Apa::STANDARD_RESPONSE_TYPES)
            ->will($this->returnValue($sxe));

        $this->mockProductParser->expects($this->once())
            ->method('ingest')
            ->will($this->returnValue(null));

        $apaBroker->process();
    }

    private function buildBroker(): Broker
    {
        return new Broker(
            $this->mockLogger           = $this->createMock(Logger::class),
            $this->mockEntityManager    = $this->createMock(EntityManager::class),
            $this->mockProductApi       = $this->createMock(ProductApi::class),
            $this->mockProductParser    = $this->createMock(ProductParser::class)
        );
    }

    private function readXML(string $file): SimpleXMLElement
    {
        $root   = self::$container->get('kernel')->getProjectDir();
        $xml    = file_get_contents("$root/tests/Fixture/$file");

        return new SimpleXMLElement($xml);
    }
}
