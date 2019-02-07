<?php

namespace App\Tests\Unit\Service\Auth;

use App\Tests\Lib\BaseTest;
use App\Service\Auth\NewstashUserProvider;

class NewstashUserProviderTest extends BaseTest
{

    public function testConstructor(): void
    {
        $nup     = self::$container->get('test.App\Service\Auth\NewstashUserProvider');

        $this->assertTrue(true);
        $this->assertInstanceOf(
            NewstashUserProvider::class,
            $nup
        );
    }
}
