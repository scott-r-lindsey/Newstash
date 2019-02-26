<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Mongo\News;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{

    /**
     * @Route("/", name="home", methods={"GET"})
     * @Template()
     */
    public function news(
        Request $request,
        News $news
    ): array
    {
        $idlt = $request->get('idlt', false);

        if ($idlt){
            $items = $news->getNews(['idlt' => $idlt ]);
        }
        else{
            $items = $news->getNews([]);
        }

        return compact('items');
    }

    /**
     * @Route("/about", name="about", methods={"GET"})
     * @Template()
     */
    public function aboutAction(): array
    {
        return [];
    }

    /**
     * @Route("/privacy", name="privacy", methods={"GET"})
     * @Template()
     */
    public function privacyAction(
        string $projectDir
    ): array
    {
        $privacy = file_get_contents($projectDir .'/privacy.html');
        return [
            'privacyhtml'   => $privacy,
        ];
    }

    /**
     * @Route("/tos", name="tos", methods={"GET"})
     * @Template()
     */
    public function tosAction(
        string $projectDir
    ): array
    {
        $tos = file_get_contents($projectDir .'/tos.html');
        return [
            'tos'   => $tos,
        ];
    }

    /**
     * @Route("/badbrowser", methods={"GET"})
     * @Template()
     */
    public function badbrowserAction(
        Request $request
    ): array
    {
        return [];
    }

    /**
     * @Route("/badbrowser/about", methods={"GET"})
     * @Template()
     */
    public function badbrowserAboutAction(
        Request $request
    ): array
    {
        return [];
    }
    /**
     * @Route("/badbrowser/privacy", methods={"GET"})
     * @Template()
     */
    public function badbrowserPrivacyAction(
        Request $request,
        string $projectDir
    ): array
    {
        return $this->privacyAction($projectDir);
    }
}
