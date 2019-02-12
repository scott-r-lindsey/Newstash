<?php
declare(strict_types=1);

namespace App\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB;
use MongoCode;
use MongoCursor;
use Psr\Log\LoggerInterface;

class Mongo
{
    private $logger;
    private $odm;
    private $defaultMongoDb;

    public function __construct(
        LoggerInterface $logger,
        DocumentManager $odm,
        string $defaultMongoDb
    )
    {
        $this->logger               = $logger;
        $this->odm                  = $odm;
        $this->defaultMongoDb       = $defaultMongoDb;
    }

    public function getDb(string $db = null): MongoDB
    {
        if (null === $db) {
            $db = $this->defaultMongoDb;
        }

        $mongoWrapper       = $this->odm->getConnection();
        $mongoWrapper->connect();
        $mongo              = $mongoWrapper->getMongo();
        return $mongo->selectDB($db);
    }


    public function setTimeout(int $timeout): void
    {
        MongoCursor::$timeout = $timeout;
    }

}
