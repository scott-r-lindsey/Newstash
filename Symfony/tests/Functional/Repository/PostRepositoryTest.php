<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Post;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Repository\PostRepository
 * @covers App\Entity\Post
 */
class PostRepositoryTest extends BaseTest
{
    protected $DBSetup = true;


    public function testFindNextPrev(): void
    {
        $em                 = self::$container->get('doctrine')->getManager();
        $postRepo           = $em->getRepository(Post::class);

        // build up some sample data ------------------------------------------
        $user       = $this->createUser();
        $one        = new Post();
        $two        = new Post();
        $three      = new Post();

        $one
            ->setUser($user)
            ->setTitle('the title')
            ->setSlug('slug-slug')
            ->setYear('1999')
            ->setDescription('so coool')
            ->setLead('above the fold')
            ->setfold('below the fold')
            ->setActive(true)
        ;

        $em->persist($one);

        $two
            ->setUser($user)
            ->setTitle('the title')
            ->setSlug('slug-slug')
            ->setYear('1999')
            ->setDescription('so coool')
            ->setLead('above the fold')
            ->setfold('below the fold')
            ->setActive(true)
        ;

        $em->persist($two);

        $three
            ->setUser($user)
            ->setTitle('the title')
            ->setSlug('slug-slug')
            ->setYear('1999')
            ->setDescription('so coool')
            ->setLead('above the fold')
            ->setfold('below the fold')
            ->setActive(true)
        ;

        $em->persist($three);

        $em->flush();

        // --------------------------------------------------------------------

        $result = $postRepo->findPreviousPost($two);

        $this->assertEquals(
            $one,
            $result
        );
        $result = $postRepo->findNextPost($two);

        $this->assertEquals(
            $three,
            $result
        );

        $result = $postRepo->findPreviousPost($one);

        $this->assertEquals(
            null,
            $result
        );
        $result = $postRepo->findNextPost($three);

        $this->assertEquals(
            null,
            $result
        );
    }
}
