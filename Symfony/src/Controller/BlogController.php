<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\ReasonRepository;
use App\Service\CommentManager;
use App\Service\FlagManager;
use App\Service\GraphQLExecutor;
use App\Service\Mongo\News;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class BlogController extends AbstractController
{

    // -- Mobile --------------------------------------------------------------

    /**
     * @Route("/blog",
     *      name="mobile_blog",
     *      condition="context.getMethod() in ['GET'] and request.headers.get('dev-only') and request.headers.get('CloudFront-Is-Mobile-Viewer')"
     * )
     * @Template("default/mobile_generic.html.twig")
     */
    public function mobileBlog(
        GraphQLExecutor $gqle
    ): array
    {
        $result = $gqle->executeQueryByName( 'posts', []);

        return ['props' => ['data' => $result['data']]];
    }

    /**
     * @Route("/blog/{post_id}/{slug}",
     *      name="mobile_post",
     *      condition="context.getMethod() in ['GET'] and request.headers.get('dev-only') and request.headers.get('CloudFront-Is-Mobile-Viewer')"
     * )
     * @Template("default/mobile_generic.html.twig")
     */
    public function mobilePost(
        GraphQLExecutor $gqle,
        string $post_id
    ): array
    {
        $result = $gqle->executeQueryByName(
            'post',
            [
                '__POST_ID__'   => $post_id
            ]
        );

        return ['props' => ['data' => $result['data']]];
    }

    // -- Desktop -------------------------------------------------------------

    /**
     * @Route("/blog", name="blog", methods={"GET"})
     * @Template()
     */
    public function indexAction(
        Request $request,
        PostManager $postManager
    ): array
    {
        return $postManager->findFrontPosts();
    }

    /**
     * @Route("/blog/{post_id}/{slug}", requirements={"post_id" = "^\d+$"}, name="blog_post", methods={"GET"})
     * @Template()
     */
    public function postAction(
        Request $request,
        PostRepository $postRepository,
        PostManager $postManager,
        int $post_id,
        string $slug
    ): array
    {

        $post = $postRepository->findActivePost($post_id);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        $correct_slug   = $post->getSlug();

        if ($slug != $correct_slug){
            $url = $this->generateUrl('blog_post', [
                'post_id'   => $post_id,
                'slug'      => $correct_slug
            ]);
            return $this->redirect($url, 301);
        }

        list ($comments, $count) = $postManager->postCommentsAsTree($post);
        $prev_post = $postRepository->findPreviousPost($post);
        $next_post = $postRepository->findNextPost($post);

        return compact('prev_post', 'next_post', 'comments', 'count', 'post');
    }

    /**
     * @Route("/blog/comments/{post_id}", requirements={"post_id" = "^\d+$"}, name="blog_post_comments", methods={"GET"})
     * @Template()
     */
    public function commentsAction(
        PostRepository $postRepository,
        PostManager $postManager,
        Request $request,
        int $post_id
    ): array
    {
        $post = $postRepository->findActivePost($post_id);

        if (!$post) {
            throw $this->createNotFoundException();
        }

        list ($comments, $count) = $postManager->postCommentsAsTree($post);
        return compact('comments', 'count', 'post');
    }

    // ------------------------------------------------------------------------
    // POSTs

    /**
     * @Route("/blog/comment/{post_id}", requirements={"post_id" = "^\d+$"}, name="blog_new_comment", methods={"POST"})
     *
     * this route is firewalled
     */
    public function newCommentAction(
        Request $request,
        CommentManager $commentManager,
        PostRepository $postRepository,
        int $post_id,
        UserInterface $user
    ): JsonResponse
    {

        $text       = $request->request->get('comment');
        $parent_id  = (int)$request->request->get('parent_id');
        $parent_id  = $parent_id ? $parent_id : null;

        $post       = $postRepository->findActivePost($post_id);
        if (!$post) {
            throw $this->createNotFoundException();
        }

        $response = new JsonResponse();

        // check text for content ---------------------------------------------
        $text = strip_tags($text);

        if (strlen($text) < 1){
            $response->setData(array(
                'error'     => 2,
                'result'    => 'Too short!'
            ));
            return $response;
        }
        else if (strlen($text) > 1000){
            $response->setData(array(
                'error'     => 1,
                'result'    => 'Too long.  No more than 1000 characters please.'
            ));
            return $response;
        }

        $comment = $commentManager->createNewComment(
            $post,
            $user,
            $request->getClientIp(),
            $request->headers->get('User-Agent'),
            $text,
            $parent_id
        );

        $reply = $this->renderView(
            'blog/comment.html.twig',
            compact('comment')
        );

        $response->setData([
            'error'         => 0,
            'text'          => $text,
            'comment_id'    => $comment->getId(),
            'reply'         => $reply
        ]);

        return $response;
    }

    /**
     * @Route("/blog/comment/flag", name="blog_new_flag", methods={"POST"})
     *
     * this route is firewalled
     */
    public function flag(
        Request $request,
        FlagManager $flagManager,
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        ReasonRepository $reasonRepository,
        UserInterface $user
    ): jsonResponse
    {
        $response   = new JsonResponse();

        $comment_id     = $request->request->get('comment_id');
        $reason_id      = $request->request->get('reason_id');
        $comment        = $commentRepository->findOneById($comment_id);
        $message        = $request->request->get('message', '');

        if ($reason_id) {
            // nullable
            $reason = $reasonRepository->findOneById($reason_id);
        }

        if (!$comment) {
            throw $this->createNotFoundException('That comment does not exist');
        }

        $flag = $flagManager->createUserCommentFlag(
            $user,
            $comment,
            $message,
            $request->headers->get('User-Agent'),
            $request->getClientIp(),
            $reason
        );

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Flag accepted'
        ));

        return $response;
    }
}
