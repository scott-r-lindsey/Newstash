<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Mongo\News;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends BaseApiController
{

    /**
     * @Route("/healthcheck")
     */
    public function healthcheckAction()
    {
        $data = [
            'healthcheck' => 'ok'
        ];

        return $this->bundleResponse($data);
    }

    /**
     * @Route("/api/v1/news")
     */
    public function news(
        Request $request,
        News $news
    ): JsonResponse
    {

        $count = 10;

        $idlt = $request->get('idlt', false);

        if ($idlt){
            $items = $news->getNews(['idlt' => $idlt, 'count' => $count +1 ]);
        }
        else{
            $items = $news->getNews(['count' => $count +1 ]);
        }

        $hasmore = false;
        if (count($items) > $count) {
            $count = array_pop($items);
            $hasmore = true;
        }

        return $this->bundleResponse(compact('items', 'hasmore'));
    }

    public function handleException(\Exception $exception)
    {
        $data = [
            'fail' => '100%'
        ];

        $code = 500;
        $message = '';

        if (method_exists($exception, 'getStatusCode')){
            $code       = $exception->getStatusCode();
            $message    = $exception->getMessage();
        }

        return $this->bundleResponse(
            $data,
            $code,
            $message
        );
    }

}
