<?php
declare(strict_types=1);

namespace App\Tests\Functional\Entity;

use PHPUnit\Framework\TestCase;
use App\Tests\Lib\BaseTest;
use App\Entity\BrowseNode;

/**
 * @covers App\Entity\BrowseNode
 */
class BrowseNodeTest extends BaseTest
{

    protected $DBSetup = true;
    private $em;

    /**
     *
     */
    public function setUp()
    {
        parent::setup();
        $this->em = self::$container->get('doctrine')->getManager();
    }

    /**
     *
     */
    public function testCreateBrowseNode(): void
    {

        $node = $this->createRootNode();

        $this->em->flush();
        $this->em->clear();

        $new_node = $this->em->getRepository(BrowseNode::class)
            ->findOneById($node->getId());

        $this->assertEquals(
            [
                $node->getId(),
                $node->getName(),
                $node->getPathData(),
                $node->getDescription(),
                $node->getSlug(),
                $node->getRoot()
            ],
            [
                $new_node->getId(),
                $new_node->getName(),
                $new_node->getPathData(),
                $new_node->getDescription(),
                $new_node->getSlug(),
                $new_node->getRoot()
            ]
        );
    }

    /**
     *
     */
    public function testCreateBrowseNodeChildren(): void
    {

        $node = $this->createRootNode();

        $child_one = $this->createNode(
            1001,
            'category one',
            'The first category',
            ['foo'],
            'cat_one',
            false
        );

        $child_two = $this->createNode(
            1002,
            'category two',
            'The second category',
            ['foo'],
            'cat_two',
            false
        );

        $node->addChildren($child_one);
        $node->addChildren($child_two);
        $this->em->flush();
        $this->em->clear();

        # ---------------------------------------------------------------------

        $child_one = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1001);

        $root = $child_one->getParents()[0];

        $this->assertEquals(
            1000,
            $root->getId()
        );

        $children = $root->getChildren();
        $this->assertEquals(
            2,
            count($children)
        );

        $child_two = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1002);

        $root->removeChildren($child_two);
        $this->em->flush();
        $this->em->clear();

        $root = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1000);

        $children = $root->getChildren();
        $this->assertEquals(
            1,
            count($children)
        );

        $child_one = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1001);
        $child_two = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1002);

        $this->assertEquals(
            1000,
            $child_one->getParents()[0]->getId()
        );
        $this->assertCount(
            0,
            $child_two->getParents()
        );

        $root->upsertChild($child_one);
        $root->upsertChild($child_two);
        $this->em->flush();
        $this->em->clear();

        $root = $this->em->getRepository(BrowseNode::class)
            ->findOneById(1000);

        $children = $root->getChildren();
        $this->assertEquals(
            2,
            count($children)
        );

    }

    # -------------------------------------------------------------------------

    /**
     *
     */
    private function createRootNode(): BrowseNode
    {
        return $this->createNode(
            1000,
            'The Root',
            'The Root Description',
            [],
            'root_slug',
            true
        );
    }

    /**
     *
     */
    private function createNode(
        int $id,
        string $name,
        string $description,
        array $pathData,
        string $slug,
        bool $root = false
    ): BrowseNode{

        $node = new BrowseNode();

        $node->setId($id)
            ->setName($name)
            ->setDescription($description)
            ->setPathData($pathData)
            ->setSlug($slug)
            ->setRoot($root);

        $this->em->persist($node);

        return $node;
    }
}
