<?php
namespace App\GraphQL\Resolver;

use App\Entity\Post;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class PostResolver implements ResolverInterface {

    private $em;
    private $commentRepository;

    public function __construct(
        EntityManagerInterface $em,
        CommentRepository $commentRepository
    )
    {
        $this->em                   = $em;
        $this->commentRepository    = $commentRepository;
    }

    public function setPostLoader(DataLoader $postLoader)
    {
        $this->postLoader = $postLoader;
    }
    public function setCommentLoader(DataLoader $commentLoader)
    {
        $this->commentLoader = $commentLoader;
    }
    public function setUserLoader(DataLoader $userLoader)
    {
        $this->userLoader = $userLoader;
    }

    // ------------------------------------------------------------------------

    public function __invoke(ResolveInfo $info, $value, Argument $args)
    {
        $method = $info->fieldName;
        return $this->$method($value, $args);
    }

    public function user(Post $post)
    {
        return $this->userLoader->load($post->getUser()->getId());
    }

    public function post(int $id)
    {
        return $this->postLoader->load($id);
    }

    public function comments(Post $post, Argument $args)
    {
        $comments =  $this->commentRepository->findPostComments($post);

        $paginator = new Paginator(function ($offset, $limit) use ($comments) {
            $slice = array_slice($comments, $offset, $limit ?? 10);

            $ret = [];

            foreach ($comments as $r) {
                $ret[] = $this->commentLoader->load($r->getId());
            }
            return $ret;
        });

        return $paginator->auto($args, count($comments));
    }

    // ------------------------------------------------------------------------
    // getters

    public function id(Post $post): int
    {
        return $post->getId();
    }

    public function active(Post $post): bool
    {
        return $post->getActive();
    }
    public function pinned(Post $post): bool
    {
        return $post->getPinned();
    }
    public function title(Post $post): string
    {
        return $post->getTitle();
    }
    public function slug(Post $post): string
    {
        return $post->getSlug();
    }
    public function year(Post $post): int
    {
        return $post->getYear();
    }
    public function image(Post $post): ?string
    {
        return $post->getImage();
    }
    public function image_x(Post $post): ?string
    {
        return $post->getImageX();
    }
    public function image_y(Post $post): ?string
    {
        return $post->getImageY();
    }
    public function description(Post $post): ?string
    {
        return $post->getDescription();
    }
    public function lead(Post $post): ?string
    {
        return $post->getLead();
    }
    public function fold(Post $post): ?string
    {
        return $post->getFold();
    }
    public function published_at(Post $post): string
    {
        return $post->getPublishedAt()->format('Y-m-d\TH:i:s.u\Z');
    }

/*
active
pinned
title
slug
year
image
image_x
image_y
description
lead
fold
published_at
*/

    // ------------------------------------------------------------------------



}
