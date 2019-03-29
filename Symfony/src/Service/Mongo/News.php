<?php
declare(strict_types=1);

namespace App\Service\Mongo;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Rating;
use App\Entity\Review;
use App\Entity\Work;
use App\Service\Mongo;
use Psr\Log\LoggerInterface;

class News{

    private $logger;
    private $mongo;

    public function __construct(
        LoggerInterface $logger,
        Mongo $mongo
    )
    {
        $this->logger               = $logger;
        $this->mongo                = $mongo;
    }

    public function getNews(
        array $args
    ): array
    {
        /* ----------------------------------------
            use cases
            -- select most recent count
            -- select count older than X
            -- select count younger than X
            -- limit by type
        ---------------------------------------- */

        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $count      = isset($args['count']) ? $args['count'] : 10;
        $and        = [];

        if (isset($args['idlt'])){
            $and[] = array(
                '_id' => array('$lt' => new \MongoId($args['idlt']))
            );
        }
        if (isset($args['idgt'])){
            $and[] = array(
                '_id' => array('$gt' => new \MongoId($args['idgt']))
            );
        }
        if (isset($args['type'])){
            $and[] = array(
                'type' => $args['type']
            );
        }

        if (count($and) == 0){
            $args = array();
        }
        else if (count($and) == 1){
            $args = $and[0];
        }
        else{
            $args = array('$and'  => $and);
        }

        $cursor = $newsCollection->find($args)
            ->sort(array('_id' => -1))
            ->limit($count);

        $items = [];
        foreach ($cursor as $doc) {
            $items[] = $doc;
        }

        return $items;
    }

    public function newPost(
        Post $post
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'post:' . $post->getId();

        $newsCollection->remove( array('sig' => $sig) );

        $user = $post->getUser();

        $record = array(
            'type'          => 'post',
            'sig'           => $sig,
            'user'          => array(
                'id'            => $user->getId(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'avatarUrl80'   => $user->getAvatarUrl(),
            ),
            'post'          => array(
                'id'            => $post->getId(),
                'slug'          => $post->getSlug(),
                'title'         => $post->getTitle(),
                'created_at'    => $post->getCreatedAt(),
                'text'          => $post->getLead(),
            )
        );

        if ($image = $post->getImage()){
            $record['post']['image'] = $image;
            $record['post']['imageX'] = $post->getImageX();
            $record['post']['imageY'] = $post->getImageY();
        }

        $newsCollection->insert($record);
    }

    public function removePost(
        Post $post
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'post:' . $post->getId();
        $newsCollection->remove( ['sig' => $sig] );
    }

    public function newComment(
        Comment $comment
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'comment:' . $comment->getId();

        $user = $comment->getUser();
        $post = $comment->getPost();

        $record = array(
            'type'          => 'comment',
            'sig'           => $sig,
            'user'          => array(
                'id'            => $user->getId(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'avatarUrl80'   => $user->getAvatarUrl(),
            ),
            'post'          => array(
                'id'            => $post->getId(),
                'slug'          => $post->getSlug(),
                'title'         => $post->getTitle(),
                'created_at'    => $post->getCreatedAt(),
            ),
            'comment'       => array(
                'id'            => $comment->getId(),
                'created_at'    => $comment->getCreatedAt(),
                'text'          => $comment->getText()
            )
        );

        if ($parent = $comment->getParent()){
            $parent_user = $parent->getUser();
            $record['parent'] = array(
                'id'        => $parent->getId(),
                'user'          => array(
                    'id'            => $parent_user->getId(),
                    'first_name'    => $parent_user->getFirstName(),
                    'last_name'     => $parent_user->getLastName(),
                    'avatarUrl80'   => $parent_user->getAvatarUrl(),
                )
            );
        }

        $newsCollection->insert($record);
    }

    public function removeComment(
        Comment $comment
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'comment:' . $comment->getId();
        $newsCollection->remove( ['sig' => $sig] );
    }

    public function newReview(
        Review $review
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $work   = $review->getWork();
        $user   = $review->getUser();
        $stars  = $review->getStars();
        $edition = $work->getFrontEdition();

        $rating_sig = 'rating:' . $work->getId() . ':' . $user->getId();
        $newsCollection->remove( array('sig' => $rating_sig) );

        $sig = 'review:' . $work->getId() . ':' . $user->getId();
        $newsCollection->remove( array('sig' => $sig) );


        $record = array(
            'type'          => 'review',
            'sig'           => $sig,
            'stars'         => $stars,
            'created_at'    => $review->getCreatedAt(),
            'review'        => array(
                'title'     => $review->getTitle(),
            ),
            'user'      => array(
                'id'            => $user->getId(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'avatarUrl80'   => $user->getAvatarUrl(),
            ),
            'work'  => array(
                'id'        => $work->getId(),
                'title'     => $work->getTitle(),
                'slug'      => $edition->getSlug(),
                'cover'     => $edition->getAmznLargeCover(),
                'coverY'    => $edition->getAmznLargeCoverY(),
                'coverX'    => $edition->getAmznLargeCoverX(),
            )
        );

        $newsCollection->insert($record);
    }

    public function removeReview(
        Review $review
    ): void
    {
        $work   = $review->getWork();
        $user   = $review->getUser();

        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'review:' . $work->getId() . ':' . $user->getId();
        $newsCollection->remove( ['sig' => $sig] );
    }

    public function newRating(
        Rating $rating
    ): void
    {
        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $work   = $rating->getWork();
        $user   = $rating->getUser();
        $stars  = $rating->getStars();
        $edition = $work->getFrontEdition();

        $sig = 'rating:' . $work->getId() . ':' . $user->getId();
        $newsCollection->remove( array('sig' => $sig) );

        $record = array(
            'type'          => 'rating',
            'sig'           => $sig,
            'stars'         => $stars,
            'user'      => array(
                'id'            => $user->getId(),
                'first_name'    => $user->getFirstName(),
                'last_name'     => $user->getLastName(),
                'avatarUrl80'   => $user->getAvatarUrl(),
            ),
            'work'  => array(
                'id'        => $work->getId(),
                'title'     => $work->getTitle(),
                'slug'      => $edition->getSlug(),
                'cover'     => $edition->getAmznLargeCover(),
                'coverY'    => $edition->getAmznLargeCoverY(),
                'coverX'    => $edition->getAmznLargeCoverX(),
            )
        );

        $newsCollection->insert($record);
    }

    public function removeRating(
        Rating $rating
    ): void
    {
        $work   = $rating->getWork();
        $user   = $rating->getUser();

        $mongodb            = $this->mongo->getDb();
        $newsCollection     = $mongodb->news;

        $sig = 'rating:' . $work->getId() . ':' . $user->getId();
        $newsCollection->remove( ['sig' => $sig] );
    }
}
