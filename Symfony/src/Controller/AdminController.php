<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\FlagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{

    /**
     * @Route("/admin", name="admin", methods={"GET"})
     * @Template()
     */
    public function index(
        Request $request,
        FlagRepository $flagRepository
    )
    {
        $flags = $flagRepository->findOutstandingFlags();

        return compact('flags');
    }

    /**
     * @Route("/admin/comment-flag/handle/{post_id}", requirements={"post_id" = "^\d+$"}, name="admin_handle_comment_flag", methods={"POST"})
     */
    public function handleCommentFlag(
        Request $request
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();

        $flag_id    = $request->request->get('flag_id');
        $action     = $request->request->get('submit');

        $flag = $em->getRepository('ScottDataBundle:Flag')
            ->findOneById($flag_id);

        $comment = $flag->getComment();

        if ('Obliterate' == $action){
            $comment->setDeleted(true);
            $newsMaster = $this->container->get('bookster_news_master');
            $newsMaster->removeComment($comment);
        }

        $flag->setSorted(true);
        $em->flush();

        return $this->redirect($this->generateUrl('admin'), 302);
        */
    }

    /**
     * @Route("/admin/review-flag/handle/{work_id}", requirements={"work_id" = "^\d+$"},  name="admin_handle_review_flag", methods={"POST"})
     */
    public function handleReviewFlag(
        Request $request
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();

        $flag_id    = $request->request->get('flag_id');
        $action     = $request->request->get('submit');

        $flag = $em->getRepository('ScottDataBundle:Flag')
            ->findOneById($flag_id);

        $review = $flag->getReview();

        if ('Obliterate' == $action){
            $review->setDeleted(true);
            $newsMaster = $this->container->get('bookster_news_master');
            $newsMaster->removeReview($review->getUser(), $review->getWork());
        }

        $flag->setSorted(true);
        $em->flush();

        return $this->redirect($this->generateUrl('admin'), 302);
        */
    }

    /**
     * @Route("/admin/blog/create", name="admin_blog_create", methods={"GET"})
     */
    public function blogCreate(
        Request $request
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();

        return array();
        */
    }

    /**
     * @Route("/admin/blog/all", name="admin_blog_index", methods={"GET"})
     * @Template()
     */
    public function blogIndex(
        Request $request
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();

        $posts = $em->getRepository('ScottDataBundle:Post')
            ->findAll();

        return compact('posts');
        */
    }

    /**
     * @Route("/admin/blog/edit/{post_id}", name="admin_blog_edit", methods={"GET", "POST"})
     * @Template()
     */
    public function blogEdit(
        Request $request,
        $post_id = false
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $user       = $this->get('security.context')->getToken()->getUser();
        $em         = $this->getDoctrine()->getManager();
        $newsMaster = $this->container->get('bookster_news_master');

        $return = array();
        $flash = '';

        if ('POST' == $request->getMethod()){
            $title          = $request->request->get('title');
            $slug           = $request->request->get('slug');
            $image          = $request->request->get('image');
            $description    = $request->request->get('description');
            $lead           = $request->request->get('lead');
            $fold           = $request->request->get('fold');
            $submit         = $request->request->get('submit');

            if (!$title){
                $flash = "Please enter a title\n";
            }
            else if (!$slug){
                $flash = "Please enter a slug\n";
            }
            else if (!$lead){
                $flash = "Please enter a lead\n";
            }
            if (!$flash){

                if ($post_id){
                    $post = $em->getRepository('ScottDataBundle:Post')
                        ->findOneById($post_id);
                }
                else{
                    $post = new Post();
                    $em->persist($post);
                }

                $post->setTitle($title)
                    ->setSlug($slug)
                    ->setDescription($description)
                    ->setLead($lead)
                    ->setFold($fold)
                    ->setUser($user);

                if ($image){
                    $root = dirname($this->get('kernel')->getRootDir());
                    $path = $root . '/web/img/blog/' . $image;

                    if (file_exists($path)){
                        list($width, $height, $type, $attr) = getimagesize($path);
                        $post->setImage($image);
                        $post->setImageX($width);
                        $post->setImageY($height);
                    }
                }

                if ('publish' == strtolower($submit)){
                    $post->setActive(true);
                    $post->setPublishedAt(new \DateTime());
                    $flash = 'The post has been published.';
                    $newsMaster->newPost($post);
                }
                else if ('unpublish' == strtolower($submit)){
                    $post->setActive(false);
                    $flash = 'The post has been unpublished.';
                }
                else{
                    $flash = 'The post has been saved.';
                }
                $em->flush();
            }

            $newsMaster = $this->container->get('bookster_news_master');
            $newsMaster->newPost($post);

            $return = compact('title', 'slug', 'image', 'lead',
                'fold', 'flash', 'post', 'description');
        }
        else{
            $post = $em->getRepository('ScottDataBundle:Post')
                ->findOneById($post_id);

            $return = array(
                'title'         => $post->getTitle(),
                'slug'          => $post->getSlug(),
                'image'         => $post->getImage(),
                'description'   => $post->getDescription(),
                'lead'          => $post->getLead(),
                'fold'          => $post->getFold(),
                'post'          => $post,
            );
        }

        return $return;
        */
    }

    /**
     * @Route("/admin/blog/preview/{post_id}", name="admin_blog_preview", methods={"GET"})
     * @Template("ScottDataBundle:Blog:post.html.twig")
     */
    public function blogPreview(
        Request $request,
        $post_id
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();
        $post    = $em->getRepository('ScottDataBundle:Post')->findOneById($post_id);

        return array(
            'post'      => $post,
            'next_post' => 0,
            'prev_post' => 0,
            'post'      => $post,
            'comments'  => array(),
            'count'     => 0
        );
        */
    }

    /**
     * @Route("/admin/blog/delete/{post_id}", name="admin_blog_delete", methods={"POST"})
     */
    public function blogDelete(
        Request $request,
        $post_id
    )
    {
        // FIXME
        /*
        $this->checkSecurity();
        $em      = $this->getDoctrine()->getManager();

        $post = $em->getRepository('ScottDataBundle:Post')
            ->findOneById($post_id);

        $newsMaster = $this->container->get('bookster_news_master');
        $newsMaster->removePost($post);

        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_blog_index'), 302);
        */
    }

    //-------------------------------------------------------------------------

    private function checkSecurity(){
        if(! $this->get('security.context')->isGranted('ROLE_ADMIN') ) {
            throw new AccessDeniedException();
        }
    }




}
