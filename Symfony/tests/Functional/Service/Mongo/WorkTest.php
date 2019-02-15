<?php

namespace App\Tests\Functional\Service\Mongo;

use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\Mongo\Work
 */
class WorkTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $exporter           = self::$container->get('test.App\Service\Export');
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $ws                 = self::$container->get('test.App\Service\Mongo\Work');

        // build up some sample data ------------------------------------------
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');
        $exporter->export();

        // --------------------------------------------------------------------

        $BusinessAndMoney = 3;

        $ret = $ws->byCategory($BusinessAndMoney);

        // --------------------------------------------------------------------

        $this->assertEquals(
            $BusinessAndMoney,
            $ret['node_id']
        );
        $this->assertCount(
            1,
            $ret['works']
        );
        $this->assertEquals(
            1,
            $ret['page']
        );
        $this->assertFalse(
            $ret['hasmore']
        );
        $this->assertCount(
            2,
            $ret['children']
        );
    }
}
