<?php

namespace App\Tests\Functional\Service\Apa;

use App\Entity\Edition;
use App\Entity\Lead;
use App\Entity\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @covers App\Service\Apa\ProductParser
 */
class ProductParserTest extends BaseTest
{
    protected $DBSetup = true;

    public function testBasic(): void
    {

        $asin           = '0674979850';

        $em             = self::$container->get('doctrine')->getManager();
        $dbh            = $em->getConnection();
        $leadManager    = self::$container->get('test.App\Service\LeadManager');

        $leadManager->newLeads([$asin]);

        $edition = $this->loadEdition('product-sample.xml', $em);

        // validate the object type
        $this->assertInstanceOf(
            Edition::class,
            $edition
        );

        // validate that the lead was marked not-new
        $lead = $em->getRepository(Lead::class)
            ->findOneByAsin($asin);

        $this->assertFalse(
            $lead->getNew()
        );

        // validate lead count
        $count = $dbh->query('SELECT COUNT(*) as c FROM xlead')->fetchAll()[0]['c'];

        $this->assertEquals(
            29,
            $count
        );

        // validate similar edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM similar_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            10,
            $count
        );

        // validate similar primary_browsenode_edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM primary_browsenode_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            8,
            $count
        );

        // validate similar browsenode_edition count
        $count = $dbh->query('SELECT COUNT(*) as c FROM browsenode_edition')->fetchAll()[0]['c'];

        $this->assertEquals(
            14,
            $count
        );

        // validate work count
        $count = $dbh->query('SELECT COUNT(*) as c FROM work')->fetchAll()[0]['c'];

        $this->assertEquals(
            1,
            $count
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

    private function loadEdition(
        string $file,
        EntityManagerInterface $em = null
    ): ?Edition
    {

        if (null === $em) {
            $em             = self::$container->get('doctrine')->getManager();
        }

        $root   = self::$container->get('kernel')->getProjectDir();
        $xml    = file_get_contents("$root/tests/Fixture/$file");

        $sxe = new SimpleXMLElement($xml);

        $this->loadBrowseNodesFromSxe($sxe->Items->Item[0], $em);

        $productParser = self::$container->get('test.App\Service\Apa\ProductParser');

        return $productParser->ingest($sxe->Items->Item[0]);
    }

    /**
     * This itterates the browsenodes in a given product xml and adds
     * them to the testing db to support the ingestion of that product
     **/
    private function loadBrowseNodesFromSxe(
        SimpleXMLElement $sxe,
        EntityManagerInterface $em
    ): void
    {

        $map        = [];
        $created    = [];

        if (($sxe->BrowseNodes) and ($sxe->BrowseNodes->BrowseNode)){
            foreach ($sxe->BrowseNodes->BrowseNode as $bn){

                $b          = $bn;
                $flip       = [];
                $flip[]     = (string)$b->BrowseNodeId;
                $map[(string)$b->BrowseNodeId] = $b;

                while (($b->Ancestors) and ($a = $b->Ancestors)){
                    $b = $a->BrowseNode;

                    $flip[] = (string)$b->BrowseNodeId;
                    $map[(string)$b->BrowseNodeId] = $b;

                    if ($b->IsCategoryRoot){
                        break;
                    }

                }

                $parent = null;
                foreach (array_reverse($flip) as $id) {
                    $bn         = $map[$id];

                    if (isset($created[$id])) {
                        $browseNode = $created[$id];
                    }
                    else{
                        $browseNode = new BrowseNode();
                        $browseNode
                            ->setId((int)$bn->BrowseNodeId)
                            ->setName((string)$bn->Name);
                        $em->persist($browseNode);
                        $created[$id] = $browseNode;
                    }

                    if ($parent){
                        $browseNode->addParent($parent);
                    }


                    $parent = $browseNode;
                }
            }

            $em->flush();
        }
    }
}
