<?php

namespace App\Tests\Functional\Service\Export;

use App\Tests\Lib\BaseTest;
use App\Service\Mongo;
use PHPUnit\Framework\TestCase;
use MongoDB;

/**
 * @covers App\Service\Export\BrowseNode
 */
class MongoTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {
        $mongo                = self::$container->get('test.App\Service\Mongo');

        $this->assertInstanceOf(
            Mongo::class,
            $mongo
        );
    }
    public function testGetDefaultDB(): void
    {
        $mongo                = self::$container->get('test.App\Service\Mongo');

        $mongoDb = $mongo->getDb();

        $this->assertInstanceOf(
            MongoDB::class,
            $mongoDb
        );
    }
}

