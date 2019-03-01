<?php

namespace App\Tests\Functional\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

class CommentManagerTest extends BaseTest
{
    protected $DBSetup = true;

    public function setUp()
    {
        parent::setup();
        $this->em           = self::$container->get('doctrine')->getManager();
        $this->manager      = self::$container->get('test.App\Service\PostManager');
    }

    public function testBasic(){
        $this->assertTrue(true);
    }

    public function testCreateNewUserComment(): void
    {
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $commentManager     = self::$container->get('test.App\Service\CommentManager');
        $mongoNews          = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

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


        $comment = $commentManager->createNewComment(
            $post,
            $user,
            '127.456.#2',
            'Some Browser.#2',
            'Great post.#2!'
        );

        // --------------------------------------------------------------------

        /*
        // comments are no longer news

        $news = $mongoNews->getNews([]);

        $this->assertCount(
            1,
            $news
        );
        */

        $this->assertEquals(
            1,
            $user->getCommentCount()
        );

        // --------------------------------------------------------------------

        $parent = $comment;
        $comment = $commentManager->createNewComment(
            $post,
            $user,
            '127.456',
            'Some Browser',
            'Great post!',
            $comment->getId()
        );

        // --------------------------------------------------------------------

        /*
        $news = $mongoNews->getNews([]);

        $this->assertCount(
            2,
            $news
        );
        */

        $this->assertEquals(
            2,
            $user->getCommentCount()
        );

        $this->assertEquals(
            '127.456',
            $comment->getIpaddr()
        );
        $this->assertEquals(
            'Some Browser',
            $comment->getUseragent()
        );
        $this->assertEquals(
            $parent,
            $comment->getParent()
        );
    }
}
