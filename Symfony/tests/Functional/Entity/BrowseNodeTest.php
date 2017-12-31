<?php

namespace App\Tests\Functional\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\BrowseNode;

/**
 * @covers BrowseNode
 */

class BrowseNodeTest extends BaseTest
{

    protected $DBSetup = true;

    public function testTrue()
    {

        $this->assertTrue(true);
    }


}
