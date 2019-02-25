<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Service\Mongo\News;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentManager
{
    private $logger;
    private $em;
    private $repo;
    private $news;

    private $reviewManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        CommentRepository $repo,
        News $news
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
        $this->news                 = $news;
    }

    public function createNewComment(
        Post $post,
        UserInterface $user,
        string $ipaddr,
        string $useragent,
        string $text,
        int $parent_id = null
    ): Comment
    {

        $comment = new Comment();
        $comment->setText($text)
            ->setIpaddr($ipaddr)
            ->setUseragent($useragent)
            ->setUser($user)
            ->setPost($post);

        if ($parent_id) {
            $parent = $this->repo->findOneById($parent_id);
            $comment->setParent($parent);
        }

        $user->setCommentCount($user->getCommentCount() +1);

        $this->em->persist($comment);
        $this->em->flush();

        $this->news->newComment($comment, $post, $user);

        return $comment;
    }
}
