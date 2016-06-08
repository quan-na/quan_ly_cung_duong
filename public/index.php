<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});
$config = require 'config.php';

$app = new \Slim\App($config);
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('app_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    $this->logger->addInfo("Something interesting happened");

    return $response;
});

$app->run();
?>
