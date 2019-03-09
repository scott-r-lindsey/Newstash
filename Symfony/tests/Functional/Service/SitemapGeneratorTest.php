<?php

namespace App\Tests\Functional\Service\Mongo;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Rating;
use App\Entity\Review;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\SitemapGenerator
 */
class SitemapGeneratorTest extends BaseTest
{
    protected $DBSetup = true;

    public function testEmpty(): void
    {
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $we                 = self::$container->get('test.App\Service\Export\Work');
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $sitemapGenerator   = self::$container->get('test.App\Service\SitemapGenerator');

        // clear out old data -------------------------------------------------
        $mongodb->works_new->drop();
        $mongodb->typeahead_work_new->drop();
        $mongodb->typeahead_author_new->drop();

        // --------------------------------------------------------------------

        $edition = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $we->exportWorks();

        // --------------------------------------------------------------------

        $files = $sitemapGenerator->generateSitemap();

        $this->assertCount(
            3,
            $files
        );
    }
}
