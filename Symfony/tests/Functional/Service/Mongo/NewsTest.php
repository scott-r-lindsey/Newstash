<?php

namespace App\Tests\Functional\Service\Mongo;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Rating;
use App\Entity\Review;
use App\Service\Export\BrowseNode;
use App\Tests\Lib\BaseTest;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Service\Mongo\News
 */
class NewsTest extends BaseTest
{
    protected $DBSetup = true;

    public function testEmpty(): void
    {
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $news               = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

        // --------------------------------------------------------------------
        $results = $news->getNews([]);

        $this->assertEquals(
            [],
            $results
        );
    }
    public function testNewPost(): void
    {
        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $user               = $this->createUser();
        $user
            ->setFirstName('joe')
            ->setLastName('blow')
        ;

        $post = $this->samplePost($user);
        $em->persist($post);

        $em->flush();
        $em->refresh($edition);
        $work = $edition->getWork();

        // --------------------------------------------------------------------

        $news->newPost($post);

        $results = $news->getNews([]);

        // --------------------------------------------------------------------

        $this->assertCount(
            1,
            $results
        );

        $result = $results[0];

        unset($result['_id']);
        unset($result['post']['created_at']);
        unset($result['user']['avatarUrl80']);

        $this->assertEquals(
            [
                "type"          => "post",
                "sig"           => "post:1",
                "user"          =>  [
                  "id"          => 1,
                  "first_name"      => "Joe",
                  "last_name"       => "Blow",
                ],
                "post"          =>  [
                  "id"              => 1,
                  "slug"            => "post-title-slug",
                  "title"           => "Post Title",
                  "text"            => "Above the fold, read more below",
                  "image"           => "some-pic.png",
                  "imageX"          => 16,
                  "imageY"          => 9,
                ]
            ],
            $result
        );

        // --------------------------------------------------------------------

        $news->removePost($post);

        $results = $news->getNews([]);

        $this->assertEquals(
            [],
            $results
        );
    }

    public function testNewComment(): void
    {

        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $user               = $this->createUser();
        $user
            ->setFirstName('joe')
            ->setLastName('blow')
        ;

        $post = $this->samplePost($user);
        $em->persist($post);

        $comment = new Comment();
        $comment
            ->setUser($user)
            ->setIpaddr('123.456')
            ->setUseragent('IE 12; like Blink')
            ->setText('Cool post!')
            ->setPost($post);

        $em->persist($comment);

        $em->flush();
        $em->refresh($edition);
        $work = $edition->getWork();

        // --------------------------------------------------------------------

        $news->newComment($comment);

        $results = $news->getNews([]);

        // --------------------------------------------------------------------

        $this->assertCount(
            1,
            $results
        );

        $result = $results[0];

        unset($result['_id']);
        unset($result['post']['created_at']);
        unset($result['comment']['created_at']);
        unset($result['user']['avatarUrl80']);

        // --------------------------------------------------------------------

        $this->assertEquals(
            [
                "type"          => "comment",
                "sig"           => "comment:1",
                "user"          =>  [
                  "id"          => 1,
                  "first_name"      => "Joe",
                  "last_name"       => "Blow",
                ],
                "post"          =>  [
                  "id"              => 1,
                  "slug"            => "post-title-slug",
                  "title"           => "Post Title",
                ],
                "comment"       => [
                  "id"              => 1,
                  "text"            => "Cool post!"
                ]
            ],
            $result
        );

        // --------------------------------------------------------------------

        $news->removeComment($comment);

        $results = $news->getNews([]);

        $this->assertEquals(
            [],
            $results
        );
    }

    public function testNewReview(): void
    {

        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $user               = $this->createUser();
        $user
            ->setFirstName('joe')
            ->setLastName('blow')
        ;

        $review = new Review();
        $review
            ->setUser($user)
            ->setWork($work)
            ->setStars(2)
            ->setTitle('good book')
            ->setText('meh')
            ->setIpaddr('123.456')
            ->setUseragent('IE 12; like Blink')
        ;

        $em->persist($review);
        $em->flush();

        // --------------------------------------------------------------------

        $news->newReview($review);

        $results = $news->getNews([]);

        // --------------------------------------------------------------------

        $this->assertCount(
            1,
            $results
        );

        $result = $results[0];

        unset($result['_id']);
        unset($result['created_at']);
        unset($result['user']['avatarUrl80']);

        // --------------------------------------------------------------------

        $this->assertEquals(
            [
              "type"            => "review",
              "sig"             => "review:1:1",
              "stars"           => 2,
              "review"          => [
                "title"             => "good book"
              ],
              "user"            => [
                "id"                => 1,
                "first_name"        => "Joe",
                "last_name"         => "Blow"
              ],
              "work"            => [
                "id"                => 1,
                "title"             => "Capital in the Twenty-First Century",
                "slug"              =>
                    "capital-in-the-twentyfirst-century-by-thomas-piketty",
                "cover"             =>
                    "https://images-na.ssl-images-amazon.com/images/I/41OKpWydb-L.jpg",
                "coverY"            => 500,
                "coverX"            => 323,
              ]
            ],
            $result
        );

        // --------------------------------------------------------------------

        $news->removeReview($review);

        $results = $news->getNews([]);

        $this->assertEquals(
            [],
            $results
        );
    }

    public function testNewRating(): void
    {

        $mongo              = self::$container->get('test.App\Service\Mongo');
        $mongodb            = $mongo->getDb();
        $em                 = self::$container->get('doctrine')->getManager();
        $workGroomer        = self::$container->get('test.App\Service\Data\WorkGroomer');
        $news               = self::$container->get('test.App\Service\Mongo\News');

        // clear out old data -------------------------------------------------
        $mongodb->news->drop();

        // build up some sample data ------------------------------------------
        $edition            = $this->loadEditionFromXML('product-sample.xml');
        $workGroomer->workGroomLogic('0674979850');

        $em->refresh($edition);
        $work = $edition->getWork();

        $user               = $this->createUser();
        $user
            ->setFirstName('joe')
            ->setLastName('blow')
        ;

        $rating = new Rating();
        $rating
            ->setUser($user)
            ->setWork($work)
            ->setStars(2)
            ->setIpaddr('123.456')
            ->setUseragent('IE 12; like Blink')
        ;

        // --------------------------------------------------------------------

        $news->newRating($rating);

        $results = $news->getNews([]);

        // --------------------------------------------------------------------

        $this->assertCount(
            1,
            $results
        );

        $result = $results[0];

        unset($result['_id']);
        unset($result['user']['avatarUrl80']);

        // --------------------------------------------------------------------

        $this->assertEquals(
            [
              "type"            => "rating",
              "sig"             => "rating:1:1",
              "stars"           => 2,
              "user"            => [
                "id"                => 1,
                "first_name"        => "Joe",
                "last_name"         => "Blow"
              ],
              "work"            => [
                "id"                => 1,
                "title"             => "Capital in the Twenty-First Century",
                "slug"              =>
                    "capital-in-the-twentyfirst-century-by-thomas-piketty",
                "cover"             =>
                    "https://images-na.ssl-images-amazon.com/images/I/41OKpWydb-L.jpg",
                "coverY"            => 500,
                "coverX"            => 323,
              ]
            ],
            $result
        );

        // --------------------------------------------------------------------

        $news->removeRating($rating);

        $results = $news->getNews([]);

        $this->assertEquals(
            [],
            $results
        );
    }



    private function samplePost(
        $user
    ): Post
    {
        $post = new Post();
        $post
            ->setUser($user)
            ->setTitle('Post Title')
            ->setSlug('post-title-slug')
            ->setYear(2019)
            ->setImage('some-pic.png')
            ->setImageX(16)
            ->setImageY(9)
            ->setDescription('This is a post')
            ->setLead('Above the fold, read more below')
            ->setFold('Hello from below the fold')
            ->setPublishedAt(new \DateTime())
        ;
        return $post;
    }
}
