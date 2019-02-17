<?php
declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Repository\WorkRepository;
use App\Repository\ReviewLikeRepository;

class AuthController extends AbstractController
{
    /**
     * @Route("/auth/profile", methods={"GET"})
     * @Template()
     */
    public function profileAction(){
        // redirected to here by login?

        // FIXME
/*
        if (!$user = $this->getUser()){
            return $this->redirect($this->generateUrl('home'));
        }
        return [];
*/
    }

    /**
     * @Route("/auth/socialreflector", methods={"GET"})
     * @Template()
     */
    public function socialReflectorAction(){
        // FIXME
        return [];
    }

    /**
     * @Route("/auth/userdrop", methods={"GET"})
     * @Template()
     */
    public function userdropAction(){
        return [];
    }

    /**
     * @Route("/auth/ingressForms", methods={"GET"})
     * @Template()
     */
    public function ingressFormsAction(){
        return [];
    }

    /**
     * @Route("/auth/userstyle.dcss", name="auth_user_style", methods={"GET"})
     */
    public function userStyleAction(
        UserInterface $user = null
    ): Response
    {
        if (!$user) {
            return $this->redirect('/css/anon-user.css');
        }

        $response = $this->render('auth/userstyle.css.twig', compact('user'));

        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }

    /**
     * @Route("/auth/review/{work_id}/like/userstyle.dcss", requirements={"work_id" = "^\d+$"}, name="auth_user_review_like_style", methods={"GET"})
     */
    public function userReviewLikeStyleAction(
        WorkRepository $workRepository,
        ReviewLikeRepository $reviewLikeRepository,
        int $work_id,
        UserInterface $user = null
    )
    {
        if (!$user) {
            return $this->redirect('/css/anon-user.css');
        }

        $work = $workRepository->findOneById($work_id);

        if (!$work){
            return new Response('', 200, array('Content-Type' => 'text/css'));
        }

        $reviewLikes = $reviewLikeRepository->findByUserAndWork(
            $work_id, $user->getId()
        );

        $response = $this->render('auth/userreviewlikes.css.twig', compact('reviewLikes'));

        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }
}
