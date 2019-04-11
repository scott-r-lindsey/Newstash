<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function findCommentCountByUser(
        UserInterface $user
    ): array
    {
        $em = $this->getEntityManager();
        $dbh = $em->getConnection();

        $sql = '
            SELECT
                count(*) as count
            FROM
                comment
            WHERE
                user_id = ? AND
                deleted = 0
        ';

        $dbh = $em->getConnection();
        $sth = $dbh->prepare($sql);
        $sth->execute([$user->getId()]);

        $result = $sth->fetch();

        return ['count'    => $result['count']];
    }

    /**
     * gets comments for a post, filter "deleted"
     **/
    public function findPostComments(
        Post $post
    ): array
    {
        return $this->findBy([
            'post'      => $post,
            'parent'    => null,
            'deleted'   => 0,
        ]);
    }
}
