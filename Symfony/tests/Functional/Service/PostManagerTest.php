<?php

namespace App\Tests\Functional\Service;

use App\Entity\Post;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class PostManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\PostManager');
    }

    public function testFrontPosts(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $postManager        = self::$container->get('test.App\Service\PostManager');

        // build up some sample data ------------------------------------------
        $user = $this->createUser();
        $post = new Post();

        $post
            ->setUser($user)
            ->setTitle('the title')
            ->setSlug('slug-slug')
            ->setYear('1999')
            ->setDescription('so coool')
            ->setLead('above the fold')
            ->setfold('below the fold')
            ->setActive(true)
        ;

        $em->persist($post);

        $em->flush();

        // --------------------------------------------------------------------

        $results = $postManager->findFrontPosts();

        $this->assertEquals(
            $post,
            $results['posts'][0]
        );

        $this->assertEquals(
            0,
            $results['post_comment_counts'][1]
        );
    }
    public function testEmptyFrontPosts(): void
    {

        $postManager        = self::$container->get('test.App\Service\PostManager');
        $results = $postManager->findFrontPosts();


        $this->assertEquals(
            [
               'posts'  => [],
               'post_comment_counts'  => []
            ],
            $results
        );
    }
}
