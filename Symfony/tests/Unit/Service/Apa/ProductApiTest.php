<?php

namespace App\Tests\Unit\Service;

use App\Service\Apa\ProductApi;
use App\Service\DelayFish;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Client;
use \GuzzleHttp\Response;
use GuzzleHttp\Psr7;

/**
 * @covers App\Service\Apa\ProductApi
 */
class ProductApiTest extends BaseTest
{
    private $mockLogger;
    private $mockClient;
    private $mockResponse;
    private $mockDelayProvider;

    const KEY_ID        = 'AKIAXXXXXXXXXXXXXXXX';
    const SECRET        = '1234567890123456789012345678901234567890';
    const ASSOC_TAG     = 'no-such-tag';
    const XML           = "<?xml version='1.0' standalone='yes'?><tags></tags>";

    public function setUp()
    {
        $this->mockLogger           = $this->createMock(LoggerInterface::class);
        $this->mockClient           = $this->createMock(Client::class);
        $this->mockResponse         = $this->createMock(ResponseInterface::class);
        $this->mockDelayProvider    = $this->createMock(DelayFish::class);
    }

    /**
     *
     */
    public function testBrowseNodeLookup()
    {
        $productApi = new ProductApi(
            $this->mockLogger,
            $this->mockClient,
            SELF::KEY_ID,
            SELF::SECRET,
            SELF::ASSOC_TAG,
            $this->mockDelayProvider
        );

        $this->mockResponse->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue(SELF::XML));

        $this->mockClient->expects($this->once())
            ->method('request')
            ->will($this->returnValue($this->mockResponse));

        $this->mockClient->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue([
                'base_uri'  => Psr7\uri_for('http://somesite.nowhere/foo/bar')
            ]));

        # ---------------------------------------------------------------------

        $sxe = $productApi->browseNodeLookup(1000);

        $this->assertInstanceOf(
            'SimpleXMLElement',
            $sxe
        );
    }
}
