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
     * @Route("/top", name="top", methods={"GET"})
     * @Template()
     */
    public function top(
        Request $request)
    {

        /*
        // FIXME

        $page = $request->query->get('page', 1);
        $page--;

        $mongodb = $this->container->get('bookstash.search.mongodb_factory')->getMongoDB();

        $works = $mongodb->works;
        $count = 25;

        $cursor = $works->find(array(
            'amzn_salesrank' => array('$gt' => 0)
        ));
        $cursor->sort(array('amzn_salesrank' => 1));
        $cursor->limit($count);
        $cursor->skip(($page * $count) +1);

        $works = array();
        foreach ($cursor as $work) {
            $works[] = $work;
        }

        return array(
            'works'     => $works,
            'count'     => $count,
            'page'      => $page,
        );
        */

        return [];
    }

    /**
     * @Route("/about", name="about", methods={"GET"})
     * @Template()
     */
    public function aboutAction(): array
    {
        // FIXME
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
    public function badbrowserAction(Request $request){
        // FIXME
        return [];
    }
    /**
     * @Route("/badbrowser/about", methods={"GET"})
     * @Template()
     */
    public function badbrowserAboutAction(Request $request){
        // FIXME
        return [];
    }
    /**
     * @Route("/badbrowser/privacy", methods={"GET"})
     * @Template()
     */
    public function badbrowserPrivacyAction(Request $request){
        // FIXME
        return $this->privacyAction();
    }
}
