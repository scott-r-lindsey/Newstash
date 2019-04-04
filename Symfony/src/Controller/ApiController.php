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
use App\Service\Mongo\Work as MongoWork;
use App\Repository\ReviewRepository;
use App\Repository\WorkRepository;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends BaseApiController
{
    const COUNT = 10;

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
        $count = self::COUNT;

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

    /**
     * @Route("/api/v1/search/books")
     */
    public function bookSearch(
        MongoWork $mongoWork,
        Request $request,
        News $news
    ): JsonResponse
    {
        $work_id        = (int)$request->query->get('work_id');
        $work_id        = $work_id ? $work_id : null;
        $page           = (int)$request->get('page', 1);
        $query_raw      = trim($request->query->get('query'));
        $count          = self::COUNT;

        $result =  $mongoWork->titleSearch($work_id, $query_raw, $count, $page);

        return $this->bundleResponse($result);
    }

    /**
     * @Route("/api/v1/search/author")
     */
    public function authorSearch(
        MongoWork $mongoWork,
        Request $request
    ): JsonResponse
    {
        $page           = (int)$request->query->get('page', 1);
        $query_raw      = trim($request->query->get('query'));
        $count          = self::COUNT;

        $result = $mongoWork->authorSearch($query_raw, $count, $page);

        return $this->bundleResponse($result);
    }

    /**
     * @Route("/api/v1/work/{work_id}")
     */
    public function work(
        SerializerInterface $serializer,
        string $work_id,
        ReviewRepository $reviewRepository,
        WorkRepository $workRepository,
        Request $request
    ): JsonResponse
    {

        $work_id = (int)$work_id;

        $work = $workRepository->getWork($work_id);

        if (!$work) {
            throw $this->createNotFoundException('The book does not exist');
        }


        //$editions       = $workRepository->getActiveEditions($work_id);
        //$similar_works  = $workRepository->getSimilarWorks($work_id);
        $bns            = $workRepository->getBrowseNodes($work);
        $review_count   = $reviewRepository->count([
            'work'      => $work_id,
            'deleted'   => 0
        ]);


        // --------------------------------------------------------------------
        return $this->bundleResponse(
            [
                'work'          => $serializer->serialize($work, 'json', [
                    'circular_reference_handler' => function ($object) {
                        return $object->getId();
                    }
                ]),
                //'similar_works',
                'bns'           => $bns,
                'review_count'  => $review_count,
                //'editions'

            ]
        );
    }

    // ------------------------------------------------------------------------

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
