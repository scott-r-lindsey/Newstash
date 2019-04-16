<?php
namespace App\GraphQL\Resolver;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class PostsResolver implements ResolverInterface {

    private $em;
    private $postRepository;

    public function __construct(
        EntityManagerInterface $em,
        PostRepository $postRepository
    )
    {
        $this->em                   = $em;
        $this->postRepository       = $postRepository;
    }

    public function setPostLoader(DataLoader $postLoader)
    {
        $this->postLoader = $postLoader;
    }

    public function __invoke(ResolveInfo $info, Argument $args)
    {

        // fixme -- move pagination into repository

        $posts =  $this->postRepository->findActivePosts();

        $paginator = new Paginator(function ($offset, $limit) use ($posts) {
            $slice = array_slice($posts, $offset, $limit ?? 10);

            $ret = [];

            foreach ($posts as $r) {
                $ret[] = $this->postLoader->load($r->getId());
            }
            return $ret;
        });

        return $paginator->auto($args, count($posts));
    }




}

