<?php

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

$envFile = dirname(__DIR__).'/.env';
if (is_file($envFile)) {
    (new Dotenv())->load($envFile);
}

$env = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'prod';
$debug = filter_var($_SERVER['APP_DEBUG'] ?? $_ENV['APP_DEBUG'] ?? ('prod' !== $env), FILTER_VALIDATE_BOOLEAN);

if ($debug) {
    Debug::enable();
}

$kernel = new AppKernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
