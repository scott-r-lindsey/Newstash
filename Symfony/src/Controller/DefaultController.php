<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @Route("/sup", name="home", methods={"GET"})
     * @Template()
     */
    public function newsAction(Request $request): array
    {

        $items = [];

        return compact('items');
    }

    /**
     * @Route("/about", name="about", methods={"GET"})
     * @Template()
     */
    public function aboutAction(): array
    {
        return array();
    }

    /**
     * @Route("/privacy", name="privacy", methods={"GET"})
     * @Template()
     */
    public function privacyAction(): array
    {

        // FIXME

        $privacy = file_get_contents($this->get('kernel')->getRootDir() . '/../web/privacy.html'); 
        return array(
            'privacyhtml'   => $privacy,
        );
    }
}
