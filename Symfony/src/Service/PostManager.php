<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

class PostManager
{
    private $logger;
    private $em;
    private $repo;

    private $ratingManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        PostRepository $repo
    )
    {
        $this->logger               = $logger;
        $this->em                   = $em;
        $this->repo                 = $repo;
    }

    public function findFrontPosts(
        int $page = 0
    ): array
    {
        $em = $this->em;

        // FIXME pagination

        $query = $em->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            WHERE p.active = 1
            ORDER BY p.created_at DESC')->setMaxResults(10);

        $posts = $query->getResult();

        // - comment count -----------------------------------------------------
        $post_comment_counts    = [];

        if (count($posts)) {
            $comment_counts         = [];
            $sql                    = '';
            $post_ids               = [];

            foreach ($posts as $post){
                if ($sql){
                    $sql .= ',';
                }
                $sql .= '?';
                $post_ids[] = (int)$post->getId();
                $post_comment_counts[$post->getId()] = 0;
            }

            $sql =
                'SELECT
                    COUNT(*) AS count, post_id
                FROM
                    comment
                WHERE
                    post_id in (' . $sql . ')
                GROUP BY post_id
            ';

            $dbh = $em->getConnection();

            $sth = $dbh->prepare($sql);
            $sth->execute($post_ids);

            while ($result = $sth->fetch()){
                $post_comment_counts[$result['post_id']] = $result['count'];
            }
        }

        return compact('posts', 'post_comment_counts');
    }


}
