<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\BaseApiController;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation\Method;

class DefaultController extends BaseApiController
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
}
