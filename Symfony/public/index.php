<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

require __DIR__.'/../vendor/autoload.php';

// ----------------------------------------------------------------------------

$app_env = $_SERVER['APP_ENV'];
$app_env = 'prod';

if ($app_debug ?? ('prod' !== ($app_env ?? 'dev'))) {
    umask(0000);

    Debug::enable();

    $kernel     = new Kernel($app_env, true);
    $request    = Request::createFromGlobals();
}
else if ($app_debug == 'prod') {
    $kernel     = new Kernel('prod', false);
    $request    = Request::createFromGlobals();

    Request::setTrustedProxies(Request::HEADER_X_FORWARDED_AWS_ELB);
}
else{
    $kernel         = new Kernel(
        $app_env ?? 'dev', $_SERVER['APP_DEBUG'] ?? ('prod' !== ($app_env ?? 'dev'))
    );

    $request        = Request::createFromGlobals();
}

// ----------------------------------------------------------------------------

/*
$kernel         = new Kernel('prod')
    $app_env ?? 'dev', $_SERVER['APP_DEBUG'] ?? ('prod' !== ($app_env ?? 'dev'))
);

$request        = Request::createFromGlobals();
Request::setTrustedProxies(Request::HEADER_X_FORWARDED_AWS_ELB);
*/


$response       = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
