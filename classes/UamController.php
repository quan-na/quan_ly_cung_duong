<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class UamController {
    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function getLoggedInUsername(Request $request, Response $response) {
        // update last access, and remove 'not remember me' cookies
        if (NULL == $_SESSION['rememberMe'] and 1800 < time() - ($_SESSION['last_access'] ?: 0)) {
            $this->ci->logger->addInfo('!!! User session expired for user : ' . $_SESSION['username']);
            UamController::logout();
        } else {
            $_SESSION['last_access'] = time();
        }
        return $_SESSION['username'];
    }

    public function authenticate(Request $request, Response $response) {
        // Do authentication, return response as controller
        $parsedBody = $request->getParsedBody();
        if (empty($parsedBody['username']) and empty($parseBody['password'])) {
            $result = array('result' => 'failed', 'reason' => 'Please input your username and password.');
        } else {
            $statement = $this->ci->db->select()->from('user_account')
                            ->where('username', '=', $parsedBody['username'])
                            ->where('enabled', '=', TRUE);
            $pdoStatement = $statement->execute();
            $user = $pdoStatement->fetch(PDO::FETCH_OBJ);
            if ($user != NULL && sha1($parsedBody['password']) == $user->password_sha1) {
                $result = array('result' => 'ok', 'name' => $user->display_name);
                $_SESSION['username'] = $user->username;
                $_SESSION['group_level'] = $user->group_level;
                if (!empty($parsedBody['rememberMe'])) {
                    $_SESSION['rememberMe'] = 'yes';
                }
                $_SESSION['last_access'] = time();
                $this->ci->logger->addInfo('--- User ' . $_SESSION['username'] . ' logged in at ' . date('Y-m-d H:i:s'));
            } else {
                $result = array('result' => 'failed', 'reason' => 'Wrong username or password.');
                $this->ci->logger->addInfo('!!! User ' . $parsedBody['username'] . ' failed login at ' . date('Y-m-d H:i:s'));
            }
        }
        return $response->withJson($result);
    }

    public static function logout() {
        $_SESSION['username'] = NULL;
        $_SESSION['group_level'] = NULL;
        $_SESSION['rememberMe'] = NULL;
        $_SESSION['last_access'] = NULL;
    }
}
?>
