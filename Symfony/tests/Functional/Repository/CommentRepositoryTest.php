<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Work;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\CommentRepository
 * @covers App\Entity\Comment
 */
class CommentRepositoryTest extends BaseTest
{
    protected $DBSetup = true;

    public function testFindArrayByUser(): void
    {

        $em                 = self::$container->get('doctrine')->getManager();
        $commentRepo        = $em->getRepository(Comment::class);

        // build up some sample data ------------------------------------------
        $user = $this->createUser();

        $post = new Post();
        $post
            ->setUser($user)
            ->setTitle('title')
            ->setActive(true)
            ->setSlug('slug')
            ->setLead('lead')
            ->setFold('fold');

        $em->persist($post);

        $comment = new Comment();
        $comment
            ->setUser($user)
            ->setPost($post)
            ->setText('Great Post')
            ->setIpaddr('127.0.0.1')
            ->setUserAgent('Some Browser');

        $em->persist($comment);

        $comment = new Comment();
        $comment
            ->setUser($user)
            ->setPost($post)
            ->setText('Great Post')
            ->setIpaddr('127.0.0.1')
            ->setUserAgent('Some Browser');

        $em->persist($comment);
        $em->flush();

        // --------------------------------------------------------------------

        $result = $commentRepo->findCommentCountByUser($user);

        $this->assertEquals(
            ['count'    => 2],
            $result
        );
    }
}
