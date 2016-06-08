<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UamController {
    protected $ci;
    //Constructor
    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }
    
    public function getLoggedInUsername(Request $request, Response $response) {
        // TODO return null if user did not logged in
        return NULL;
    }
    
    public function authenticate(Request $request, Response $response) {
        // TODO do authentication, return response as controller
        $parsedBody = $request->getParsedBody();
        $result = array('result' => 'ok', 'url' => '/html/home.html'); // TODO business processing
        return $response->withJson($result);
    }
}
?>
