<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \UamController;

require '../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});
$config = require 'config.php';

$app = new \Slim\App($config);
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('app_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("/var/log/qlcd/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new \Slim\PDO\Database("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'] . ';charset=utf8',
                   $db['user'], $db['password']);
    // TODO when default options is not enough, change this
    return $pdo;
};

$app->add(function ($request, $response, $next) {
    $this->logger->addInfo(">>> authentication interception");
    ini_set('session.gc_maxlifetime', 172800);
    session_set_cookie_params(172800);
    session_cache_limiter(false);
    session_start();
    if (!in_array($request->getRequestTarget(), array('/', '/authenticate'))
        && NULL == (new UamController($this))->getLoggedInUsername($request, $response)) { 
        $this->logger->addInfo("!!! not logged in");
        $response = $response->withStatus(403, 'Not authenticated.');
    } else {
        $response = $next($request, $response);
    }
    $this->logger->addInfo("<<< authentication interception");

	  return $response;
});

$app->get('/', function (Request $request, Response $response) {
    return $response->withRedirect('/html/home.html', 303);
});

$app->post('/authenticate', function (Request $request, Response $response) {
    return (new UamController($this))->authenticate($request, $response);
});

$app->post('/logout', function (Request $request, Response $response) {
    UamController::logout();
    return $response->withJson(array('result' => 'ok'));
});

$app->post('/menu/list', '\MenuController:menuList');

$app->run();
?>
