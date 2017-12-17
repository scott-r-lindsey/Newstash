<?php

namespace App\Tests\Lib;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class BaseTest extends WebTestCase
{
    protected $db_setup = true;
    protected $client;
    protected $container;

    public function setup()
    {

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();

        parent::setup();
    }
}
