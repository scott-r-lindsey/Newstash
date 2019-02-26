<?php
require 'vendor/autoload.php';

use MongoDB\Client;
use MongoDB\Driver\Exception\ConnectionTimeoutException;

// same as this in the console:
//  db.session.ensureIndex( { "expires_at": 1 }, { expireAfterSeconds: 0 } )

print ("creating mongo session index if missing...\n");
$tries = 0;

while ($tries < 20) {
    $tries ++;
    try {

        $client      = new Client(getenv('MONGODB_URL')); 
        $client->dbSession->session->createIndex(["expires_at" => 1], ["expireAfterSeconds" => 0 ]);
        exit;

    } catch (ConnectionTimeoutException $e) {
        print ("failed connecting to mongo, sleeping one second...\n");
        sleep (1);
    }
}
