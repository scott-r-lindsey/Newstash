<?php
namespace App\GraphQL\Resolver;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoader;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;

class CommentResolver implements ResolverInterface {

    private $em;
    private $commentRepository;

    public function __construct(
        EntityManagerInterface $em,
        CommentRepository $commentRepository
    )
    {
        $this->em           = $em;
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

    public function user(Comment $comment)
    {
        return $this->userLoader->load($comment->getUser()->getId());
    }

    public function comment(int $id)
    {
        return $this->commentLoader->load($id);
    }

    public function replies(Comment $comment, Argument $args)
    {
        $replies = $comment->getReplies();

        $paginator = new Paginator(function ($offset, $limit) use ($replies) {
            $slice = $replies->slice($offset, $limit ?? 10);

            $ret = [];

            foreach ($replies as $r) {
                $ret[] = $this->commentLoader->load($r->getId());
            }
            return $ret;
        });

        return $paginator->auto($args, count($replies));
    }

    // fixme user

    // ------------------------------------------------------------------------
    // getters

    public function id(Comment $comment): int
    {
        return $comment->getId();
    }
    public function text(Comment $comment): string
    {
        return $comment->getText();
    }
    public function created_at(Comment $comment): string
    {
        return $comment->getCreatedAt()->format('Y-m-d\TH:i:s.u\Z');
    }
}
