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
     * @Route("/", name="home", methods={"GET"})
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
    public function privacyAction(
        string $projectDir
    ): array
    {

        // FIXME

        $privacy = file_get_contents($projectDir .'/privacy.html');
        return [
            'privacyhtml'   => $privacy,
        ];
    }
}
