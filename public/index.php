<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \UamController;

require '../vendor/autoload.php';
spl_autoload_register(function ($classname) {
    require ("../classes/" . $classname . ".php");
});
$config = require 'config.php';

$app = new \Slim\Slim($config);
$container = $app->container;

$container->singleton('logger', function($c) {
    $logger = new \Monolog\Logger('app_logger');
    $file_handler = new \Monolog\Handler\StreamHandler($c['settings']['log']['location']);
    $logger->pushHandler($file_handler);
    return $logger;
});

$container->singleton('db', function ($c) {
    $db = $c['settings']['db'];
    $pdo = new \Slim\PDO\Database("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'] . ';charset=utf8',
                   $db['user'], $db['password']);
    // TODO when default options is not enough, change this
    return $pdo;
});

// TODO create middleware class
class AuthenticationMiddleware extends \Slim\Middleware {
    public function __construct() {}
    public function call() {
        $this->app->logger->addInfo(">>> authentication interception");
        ini_set('session.gc_maxlifetime', 172800);
        session_set_cookie_params(172800);
        session_cache_limiter(false);
        session_start();
        $uamController = new UamController($this->app);
        $this->app->logger->addInfo("--- url : " . $this->app->request()->getPath());
        if (!in_array($this->app->request()->getPath(), array('/', '/authenticate', '/logout'))
            && NULL == $uamController->getLoggedInUsername()) { 
            $this->app->logger->addInfo("!!! not logged in");
            $this->app->response()->status(403);
            $this->app->response()->body('Not authenticated.');
        } else {
            $this->next->call();
        }
        $this->app->logger->addInfo("<<< authentication interception");
    }
}
$authMiddleware = new AuthenticationMiddleware();
$app->add($authMiddleware);

$app->get('/', function () use ($app) {
    $app->redirect('/html/home.html');
});

$app->post('/authenticate', function () use ($app) {
    $uamController = new UamController($app);
    return $uamController->authenticate();
});

$app->post('/logout', function () use ($app) {
    UamController::logout();
    $app->response()->status(200);
    $app->response()->body(json_encode(array('result' => 'ok')));
});

$app->container->singleton('MenuController', function () use ($app) {
    return new \MenuController($app);
});
$app->post('/menu/list', function () use ($app){
    $app->MenuController->menuList();
});

$app->container->singleton('PhatTuController', function ($container) use ($app) {
    return new \PhatTuController($app);
});
$app->post('/phat_tu/list', function () use ($app){
    $app->PhatTuController->phatTuList();
});
$app->post('/phat_tu/delete', function () use ($app){
    $app->PhatTuController->phatTuDelete();
});
$app->post('/phat_tu/get', function () use ($app){
    $app->PhatTuController->phatTuGet();
});
$app->post('/phat_tu/save', function () use ($app){
    $app->PhatTuController->phatTuSave();
});

$app->post('/muc_cung_duong/list', '\MucCungDuongController:mucCungDuongList');
$app->post('/muc_cung_duong/delete', '\MucCungDuongController:mucCungDuongDelete');
$app->post('/muc_cung_duong/get', '\MucCungDuongController:mucCungDuongGet');
$app->post('/muc_cung_duong/save', '\MucCungDuongController:mucCungDuongSave');

$app->post('/cung_duong/list', '\CungDuongController:cungDuongList');
$app->post('/cung_duong/delete', '\CungDuongController:cungDuongDelete');
$app->post('/cung_duong/get', '\CungDuongController:cungDuongGet');
$app->post('/cung_duong/save', '\CungDuongController:cungDuongSave');
$app->post('/cung_duong/report', '\CungDuongController:cungDuongReport');

$app->post('/user/get_current', '\UserController:userGetCurrent');
$app->post('/user/change_password', '\UserController:userChangePassword');

$app->run();
?>
