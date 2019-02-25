<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Service\CommentManager;
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
     */
    public function newCommentAction(
        Request $request,
        CommentManager $commentManager,
        PostRepository $postRepository,
        int $post_id,
        UserInterface $user = null
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

        if(!$user) {
            $response->setData([
                'error' => 1,
                'result' => 'You\'re not logged in.'
            ]);
            return $response;
        }

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
     */
    public function flag(
        Request $request
    ): array
    {

        // FIXME
        /*

        $em         = $this->getDoctrine()->getManager();

        $reason_id  = $request->request->get('reason_id');
        $response   = new JsonResponse();

        if (0 == $reason_id){
            $reason = false;
        }
        else{
            $reason = $em->getRepository('ScottDataBundle:Reason')
                        ->findOneById($reason_id);

            if (!$reason){
                $response->setData(array(
                    'error'     => 1,
                    'result'    => 'The reason does not exist'
                ));
                return $response;
            }
        }

        $comment_id     = $request->request->get('comment_id');
        $comment        = $em->getRepository('ScottDataBundle:Comment')
                            ->findOneById($comment_id);

        if (!$comment_id){
            $response->setData(array(
                'error'     => 1,
                'result'    => 'The comment does not exist'
            ));
            return $response;
        }

        $message        = $request->request->get('message');
        $ipaddr         = $request->server->get('REMOTE_ADDR');
        $useragent      = $request->server->get('HTTP_USER_AGENT');

        $flag = new Flag();
        $flag->setComment($comment);
        $flag->setMessage($message);
        $flag->setUseragent($useragent);
        $flag->setIpaddr($ipaddr);

        if ($reason){
            $flag->setReason($reason);
        }

        if (($this->container->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY') ) ||
            ( $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') )){
            $flag->setUser($this->get('security.context')->getToken()->getUser());
        }

        $em->persist($flag);
        $em->flush();

        $response->setData(array(
            'error'     => 0,
            'result'    => 'Flag accepted'
        ));
        return $response;
        */
    }
}
