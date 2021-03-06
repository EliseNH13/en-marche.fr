<?php

declare(strict_types=1);

if ($_SERVER['REQUEST_URI'] === '/health') {
    header('HTTP/1.0 200 OK');

    echo 'Healthy';
    exit;
}

if (!empty($_SERVER['SYMFONY__BASIC_AUTH_USER']) && !empty($_SERVER['SYMFONY__BASIC_AUTH_PASSWORD'])) {
    if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) || $_SERVER['PHP_AUTH_USER'] !== $_SERVER['SYMFONY__BASIC_AUTH_USER'] || $_SERVER['PHP_AUTH_PW'] !== $_SERVER['SYMFONY__BASIC_AUTH_PASSWORD']) {
        header('HTTP/1.0 401 Unauthorized');
        header('WWW-Authenticate: Basic realm="Password required"');

        echo 'Unauthorized';
        exit;
    }
}

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
