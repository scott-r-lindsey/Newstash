<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findPreviousPost(
        Post $post
    ): ?Post
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            WHERE
                p.active = 1 AND
                p.id < :id
            ORDER BY
                p.id DESC')->setMaxResults(1);

        $query->setParameter('id', $post->getId());

        return $query->getOneOrNullResult();
    }

    public function findNextPost(
        $post
    ): ?Post
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            WHERE
                p.active = 1 AND
                p.id > :id
            ORDER BY
                p.id DESC')->setMaxResults(1);

        $query->setParameter('id', $post->getId());

        return $query->getOneOrNullResult();
    }

    public function findActivePost(
        int $post_id
    ): Post
    {
        return $this->findOneBy([
            'id'        => $post_id,
            'active'    => 1
        ]);
    }

    public function findActivePosts()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT p
            FROM App\Entity\Post p
            WHERE
                p.active = 1
            ORDER BY
                p.pinned DESC, p.id DESC');

        return $query->getResult();
    }
}
