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

}
