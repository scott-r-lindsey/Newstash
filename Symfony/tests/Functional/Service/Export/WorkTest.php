<?php

namespace App\Tests\Functional\Service\Export;

use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\Export\Work
 */
class WorkTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $we                 = self::$container->get('test.App\Service\Export\Work');
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $mongodb            = $mongo->getDb();

        // clear out old data -------------------------------------------------
        $mongodb->works_new->drop();
        $mongodb->typeahead_work_new->drop();
        $mongodb->typeahead_author_new->drop();

        // build up some sample data ------------------------------------------
        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        // --------------------------------------------------------------------
        $we->exportWorks();
        // --------------------------------------------------------------------

        // works
        $cursor = $mongodb->works_new->find();

        $this->assertEquals(
            1,
            $cursor->count()
        );

        $work = $cursor->getNext();

        $this->assertEquals(
            'Capital in the Twenty-First Century',
            $work['title']
        );

        // typeahead work
        $cursor = $mongodb->typeahead_work_new->find();

        $this->assertEquals(
            1,
            $cursor->count()
        );
        $typeahead_work = $cursor->getNext();

        $this->assertEquals(
            'Capital in the Twenty-First Century',
            $typeahead_work['display']
        );


        // typeahead author
        $cursor = $mongodb->typeahead_author_new->find();

        $this->assertEquals(
            1,
            $cursor->count()
        );

        $typeahead_author = $cursor->getNext();

        $this->assertEquals(
            'Thomas Piketty',
            $typeahead_author['display']
        );

        // clear out old data -------------------------------------------------
        $mongodb->works_new->drop();
        $mongodb->typeahead_work_new->drop();
        $mongodb->typeahead_author_new->drop();
    }
}
