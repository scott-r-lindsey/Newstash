<?php
declare(strict_types=1);

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Exception;
use BadMethodCallException;

/**
 *
 */
abstract class BaseApiController extends AbstractFOSRestController
{
    protected function bundleResponse(
        array $result,
        int $code = 200,
        string $message = 'Ok'
    ): Response 
    {

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

    protected function legacyResponse(
        array $data,
        int $code = 200,
        string $message = 'Ok'
    ): Response
    {
        $response = new JsonResponse($data);
        $response->setStatusCode($code);
        return $response;
    }
}

