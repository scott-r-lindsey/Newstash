<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AuthController extends AbstractController
{

    /**
     * @Route("/sup", name="home", methods={"GET"})
     * @Template()
     */
    public function newsAction(Request $request)
    {

        $items = [];

        return compact('items');
    }

    /**
     * @Route("/auth/userstyle.css", name="auth_user_style", methods={"GET"})
     */
    public function userStyleAction()
    {

/*

        if (!$user = $this->getUser()){
            return $this->redirect('/css/anon-user.css');
        }

        $style = $this->container->get('templating')->render(
            'ScottDataBundle:Auth:userstyle.css.twig',
            array('user' => $user)
        );
*/

        $style = '';

        return new Response($style, 200, array('Content-Type' => 'text/css'));
    }


    /**
     * @Route("/auth/review/{work_id}/like/userstyle.css", requirements={"work_id" = "^\d+$"}, name="auth_user_review_like_style", methods={"GET"})
     */
    public function userReviewLikeStyleAction($work_id)
    {

/*

        $em         = $this->getDoctrine()->getManager();

        if (!$user = $this->getUser()){
            return $this->redirect('/css/anon-user.css');
        }

        $work = $em->getRepository('ScottDataBundle:Work')
            ->findOneById($work_id);

        if (!$work){
            return new Response('', 200, array('Content-Type' => 'text/css'));
        }

        $dql = '
            SELECT rl
            FROM
                Scott\DataBundle\Entity\ReviewLike rl
            JOIN rl.review r
            WHERE
                rl.user = :user AND
                r.work = :work';

        $query = $em->createQuery($dql);
        $query->setHint(\Doctrine\ORM\Query::HINT_INCLUDE_META_COLUMNS, true)
            ->setParameter('work', $work_id)
            ->setParameter('user', $user);

        $reviewLikes = $query->getArrayResult();

        $style = $this->container->get('templating')->render(
            'ScottDataBundle:Auth:userreviewlikes.css.twig',
            compact('reviewLikes')
        );

        return new Response($style, 200, array('Content-Type' => 'text/css'));
*/

    }




}
