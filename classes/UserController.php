<?php 

class UserController {
    protected $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    public function userGetCurrent() {
        $statement = $this->app->db->select(array('username', 'display_name'))->from('user_account')
                          ->where('username', '=', $_SESSION['username']);
        $pdoStatement = $statement->execute();
        $currUser = $pdoStatement->fetch(PDO::FETCH_OBJ);
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($currUser));
    }

    public function userChangePassword() {
        $returnObj = array();
        $parsedBody = json_decode($this->app->request()->getBody());
        $this->app->logger->addInfo('--- Password change request received : ' . json_encode($parsedBody));
        // validate
        if (empty($parsedBody->oldPassword) || empty($parsedBody->newPassword) || empty($parsedBody->retypePassword)) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'All three passwords are required.';
        } else {
            $oldPassStatement = $this->app->db->select(array('password_sha1'))->from('user_account')
                                     ->where('username', '=', $_SESSION['username']);
            $pdoOldPassStmt = $oldPassStatement->execute();
            $oldPassObj = $pdoOldPassStmt->fetch(PDO::FETCH_OBJ);
            if ($oldPassObj->password_sha1 != sha1($parsedBody->oldPassword)) {
                $returnObj['result'] = 'error';
                $returnObj['message'] = 'Old password is not matched.';
            } else {
                $updateStatement = $this->app->db->update(array('password_sha1' => sha1($parsedBody->newPassword)))
                                        ->table('user_account')
                                        ->where('username', '=', $_SESSION['username']);
                $rows = $updateStatement->execute();
                $returnObj['result'] = 'ok';
                $returnObj['rows'] = $rows;
            }
        }
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($returnObj));
    }
}

?>
