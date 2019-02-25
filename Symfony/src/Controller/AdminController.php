<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Work;
use App\Repository\FlagRepository;
use App\Repository\PostRepository;
use App\Service\Mongo\News;
use App\Service\PostManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin", methods={"GET"})
     * @Template()
     */
    public function index(
        FlagRepository $flagRepository
    )
    {
        $flags = $flagRepository->findOutstandingFlags();

        return compact('flags');
    }

    /**
     * @Route("/admin/comment-flag/handle/{post}", requirements={"post" = "^\d+$"}, name="admin_handle_comment_flag", methods={"POST"})
     */
    public function handleCommentFlag(
        Request $request,
        FlagRepository $flagRepository,
        EntityManagerInterface $em,
        News $news
    )
    {
        $flag_id    = $request->request->get('flag_id');
        $action     = $request->request->get('submit');

        $flag = $flagRepository->findOneById($flag_id);

        $comment = $flag->getComment();

        if ('Obliterate' == $action){
            $comment->setDeleted(true);
            $news->removeComment($comment);
        }

        $flag->setSorted(true);
        $em->flush();

        return $this->redirect($this->generateUrl('admin'), 302);
    }

    /**
     * @Route("/admin/review-flag/handle/{work_id}", requirements={"work_id" = "^\d+$"},  name="admin_handle_review_flag", methods={"POST"})
     */
    public function handleReviewFlag(
        Request $request,
        FlagRepository $flagRepository,
        EntityManagerInterface $em,
        News $news
    )
    {
        $flag_id    = $request->request->get('flag_id');
        $action     = $request->request->get('submit');

        $flag = $flagRepository->findOneById($flag_id);

        $review = $flag->getReview();

        if ('Obliterate' == $action){
            $review->setDeleted(true);
            $news->removeReview($review);
        }

        $flag->setSorted(true);
        $em->flush();

        return $this->redirect($this->generateUrl('admin'), 302);
    }

    /**
     * @Route("/admin/blog/create", name="admin_blog_create", methods={"GET"})
     * @Template("admin/blog_edit.html.twig")
     */
    public function blogCreate(
    ): array
    {
        return [];
    }

    /**
     * @Route("/admin/blog/all", name="admin_blog_index", methods={"GET"})
     * @Template()
     */
    public function blogIndex(
        PostRepository $postRepository
    ): array
    {
        return ['posts' => $postRepository->findAll()];
    }

    /**
     * @Route("/admin/blog/edit/{post}", name="admin_blog_edit", methods={"GET", "POST"})
     * @Template()
     */
    public function blogEdit(
        UserInterface $user,
        EntityManagerInterface $em,
        PostRepository $postRepository,
        News $news,
        Request $request,
        Post $post = null
    )
    {

        $return     = [];
        $flash      = '';

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
                if (!$post){
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
                    $em->flush();
                    $news->newPost($post);
                }
                else if ('unpublish' == strtolower($submit)){
                    $post->setActive(false);
                    $flash = 'The post has been unpublished.';
                    $news->removePost($post);
                }
                else{
                    $flash = 'The post has been saved.';
                }
                $em->flush();

            }

            return compact(
                'description',
                'flash',
                'fold',
                'image',
                'lead',
                'post',
                'slug',
                'title'
            );
        }

        return [
            'description'   => $post->getDescription(),
            'fold'          => $post->getFold(),
            'image'         => $post->getImage(),
            'lead'          => $post->getLead(),
            'post'          => $post,
            'slug'          => $post->getSlug(),
            'title'         => $post->getTitle(),
        ];
    }

    /**
     * @Route("/admin/blog/preview/{post}", name="admin_blog_preview", methods={"GET"})
     * @Template("blog/post.html.twig")
     */
    public function blogPreview(
        PostRepository $postRepository,
        Post $post
    ): array
    {
        return [
            'post'      => $post,
            'next_post' => 0,
            'prev_post' => 0,
            'comments'  => [],
            'count'     => 0
        ];
    }

    /**
     * @Route("/admin/blog/delete/{post}", name="admin_blog_delete", methods={"POST"})
     */
    public function blogDelete(
        EntityManagerInterface $em,
        News $news,
        Request $request,
        Post $post
    )
    {
        $em      = $this->getDoctrine()->getManager();

        $news->removePost($post);

        $em->remove($post);
        $em->flush();

        return $this->redirect($this->generateUrl('admin_blog_index'), 302);
    }
}
