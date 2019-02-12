<?php

namespace App\Tests\Functional\Service\Export;

use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\Export\BrowseNode
 */
class BrowseNodeTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $bne                = self::$container->get('test.App\Service\Export\BrowseNode');
        $mbn                = self::$container->get('test.App\Service\Mongo\BrowseNode');
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();

        $mongodb->nodes_new->drop();

        $this->assertInstanceOf(
            BrowseNode::class,
            $bne
        );

        // just get a little sample data in there
        $edition        = $this->loadEditionFromXML('product-sample.xml');

        $returnedNodes  = $bne->exportNodes();
        $mongoNodes     = $mbn->findNodesById([1000], 'nodes_new');

        $this->assertEquals(
            $mongoNodes[0]['name'],
            'Subjects'
        );

        $mongodb->nodes_new->drop();
    }
}
