<?php
declare(strict_types=1);

namespace App\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;           
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;
use BadMethodCallException;             

/**
 *
 */
class BaseApiController extends FOSRestController
{
    protected function bundleResponse(
        array $result,
        int $code = 200,
        string $message = 'Ok'
    ): Response {

        $time = time();

        $response_data = [
            'status'    => [
                'message'       => $message,
                'code'          => $code       
            ],
            'timestamp'     => $time,
            'date'          => date(\DateTime::ISO8601, $time),
            'result'        => $result
        ];

        $response = new JsonResponse($response_data);
        $response->setStatusCode($code);
        return $response;
    }
}

