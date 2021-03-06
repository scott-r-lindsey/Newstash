<?php
declare(strict_types=1);

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\BaseApiController;

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
