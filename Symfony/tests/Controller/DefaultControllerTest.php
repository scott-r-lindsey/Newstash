<?php
declare(strict_types=1);

namespace App\Tests;    

use PHPUnit\Framework\TestCase;         
use App\Tests\Lib\BaseTest;             

class DefaultControllerTest extends BaseTest
{

    public function testHealthCheck()
    {
        $response   = $this->doNumJsonQuery('/healthcheck', 'GET', 200);
        $data       = json_decode($response->getContent(), true);

        $this->validateUnsetBasic($data);

        $this->assertEquals(
            [
                'status'    => [
                    'message'   => 'Ok',
                    'code'      => 200,
                ],
                'result'    => [
                    'healthcheck' => 'ok'
                ],

            ],
            $data
        );
    }
}
