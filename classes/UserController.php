<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response; 

class UserController {
    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function userGetCurrent($request, $response, $args) {
        $statement = $this->ci->db->select(array('username', 'display_name'))->from('user_account')
                          ->where('username', '=', $_SESSION['username']);
        $pdoStatement = $statement->execute();
        $currUser = $pdoStatement->fetch(PDO::FETCH_OBJ);
        return $response->withJson($currUser);
    }

    public function userChangePassword($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Password change request received : ' . json_encode($parsedBody));
        // validate
        if (!$parsedBody['oldPassword'] || !$parsedBody['newPassword'] || !$parsedBody['retypePassword']) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'All three passwords are required.';
        } else {
            $oldPassStatement = $this->ci->db->select(array('password_sha1'))->from('user_account')
                                     ->where('username', '=', $_SESSION['username']);
            $pdoOldPassStmt = $oldPassStatement->execute();
            $oldPassObj = $pdoOldPassStmt->fetch(PDO::FETCH_OBJ);
            if ($oldPassObj->password_sha1 != sha1($parsedBody['oldPassword'])) {
                $returnObj['result'] = 'error';
                $returnObj['message'] = 'Old password is not matched.';
            } else {
                $updateStatement = $this->ci->db->update(array('password_sha1' => sha1($parsedBody['newPassword'])))
                                        ->table('user_account')
                                        ->where('username', '=', $_SESSION['username']);
                $rows = $updateStatement->execute();
                $returnObj['result'] = 'ok';
                $returnObj['rows'] = $rows;
            }
        }
        return $response->withJson($returnObj);
    }
}

?>
