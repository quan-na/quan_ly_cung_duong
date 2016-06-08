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

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
                   $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");
    $this->logger->addInfo("Something interesting happened");

    return $response;
});

$app->get('/', function (Request $request, Response $response) {
    return $response->withRedirect('/html/login.html', 303);
});

$app->post('/authenticate', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();
    $result = array('result' => 'ok', 'url' => '/html/home.html'); // TODO business processing
    return $response->withJson($result);
});

$app->run();
?>
