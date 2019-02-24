<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PostManager;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
        $post_id,
        $slug
    ): array
    {

        // FIXME
        /*

        $em = $this->getDoctrine()->getManager();

        $postManager    = $this->get('bookstash.blog.post_manager');
        $post           = $postManager->getPost($post_id);
        $correct_slug   = $post->getSlug();

        if ($slug != $correct_slug){
            $url = $this->generateUrl('blog_post', array(
                'post_id'   => $post_id,
                'slug'      => $correct_slug));
            return $this->redirect($url, 301);
        }

        list ($comments, $count) = $postManager->postCommentsAsTree($post);
        $prev_post = $postManager->findPreviousPost($post);
        $next_post = $postManager->findNextPost($post);

        return compact('prev_post', 'next_post', 'comments', 'count', 'post');
        */
    }


    /**
     * @Route("/blog/comments/{post_id}", requirements={"post_id" = "^\d+$"}, name="blog_post_comments", methods={"GET"})
     * @Template()
     */
    public function commentsAction(
        Request $request,
        $post_id
    ): array
    {

        // FIXME
        /*

        $em = $this->getDoctrine()->getManager();

        $postManager    = $this->get('bookstash.blog.post_manager');
        $post           = $postManager->getPost($post_id);

        list ($comments, $count) = $postManager->postCommentsAsTree($post);
        return compact('comments', 'count', 'post');
        */
    }

    // ------------------------------------------------------------------------
    // POSTs

    /**
     * @Route("/blog/comment/{post_id}", requirements={"post_id" = "^\d+$"}, name="blog_new_comment", methods={"POST"})
     */
    public function newCommentAction(
        Request $request,
        $post_id
    ): array
    {

        // FIXME
        /*

        $em             = $this->getDoctrine()->getManager();
        $user           = $this->get('security.context')->getToken()->getUser();
        $postManager    = $this->get('bookstash.blog.post_manager');
        $post           = $postManager->getPost($post_id);

        // FIXME
        // prevent replies to deleted

        // FIXME refactor to service

        $response = new JsonResponse();
        if(! $this->get('security.context')->isGranted('ROLE_USER') ) {
            $response->setData(array(
                'error' => 1,
                'result' => 'You\'re not logged in.'
            ));
            return $response;
        }

        $text       = $request->request->get('comment');
        $parent_id  = $request->request->get('parent_id');

        $parent = false;
        if ($parent_id){
            $parent = $em->getRepository('ScottDataBundle:Comment')
                ->findOneById($parent_id);
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

        // --------------------------------------------------------------------
        $comment = new Comment();
        $comment->setText($text)
            ->setIpaddr($request->getClientIp())
            ->setUseragent($request->headers->get('User-Agent'))
            ->setUser($user)
            ->setPost($post);

        if ($parent){
            $comment->setParent($parent);
        }

        $user->setCommentCount($user->getCommentCount() +1);

        $em->persist($comment);
        $em->flush();

        $newsMaster = $this->container->get('bookster_news_master');
        $newsMaster->newComment($comment, $post, $user);

        $reply = $this->container->get('templating')->render(
            'ScottDataBundle:Blog:comment.html.twig',
            array('comment' => $comment)
        );

        $response->setData(array(
            'error'         => 0,
            'text'          => $text,
            'comment_id'    => $comment->getId(),
            'reply'         => $reply
        ));
        return $response;
        */
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
