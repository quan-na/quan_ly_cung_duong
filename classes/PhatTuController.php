<?php

class PhatTuController {
    protected $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    public function phatTuList() {
        $returnObj = array();
        $parsedBody = $this->app->request()->post();
        $statement = $this->app->db->select()->from('phat_tu')
                          ->limit($parsedBody['rowCount'],
                                  $parsedBody['rowCount'] * ($parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
                $statement->orderBy($key, $direction);
        $countStatement = $this->app->db->select(array('COUNT(id)'))->from('phat_tu');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            $statement->where("CONCAT('#',id,'#',CONCAT_WS(',',name,phap_danh,phone,email))", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
            $countStatement->where("CONCAT('#',id,'#',CONCAT_WS(',',id,name,phap_danh,phone,email))", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
        }
        $pdoStatement = $statement->execute();
        $countPdoStatement = $countStatement->execute();
        $phatTuArray = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        $total = $countPdoStatement->fetch(PDO::FETCH_NUM);
        $returnObj['current'] = $parsedBody['current'];
        $returnObj['rowCount'] = $parsedBody['rowCount'];
        $returnObj['total'] = $total[0];
        $returnObj['rows'] = $phatTuArray;
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($returnObj));
    }

    public function phatTuDelete() {
        $returnObj = array();
        $parsedBody = json_decode($this->app->request()->getBody());
        $this->app->logger->addInfo('--- Phat tu delete request received : ' . json_encode($parsedBody));
        // TODO validate
        $deleteStatement = $this->app->db->delete()
                                ->from('phat_tu')
                                ->where('id','=',$parsedBody->id);
        $returnObj['rows'] = $deleteStatement->execute();
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($returnObj));
    }

    public function phatTuGet() {
        $parsedBody = json_decode($this->app->request()->getBody());
        $statement = $this->app->db->select()->from('phat_tu')
                          ->where('id', '=', $parsedBody->id);
        $pdoStatement = $statement->execute();
        $phatTu = $pdoStatement->fetch(PDO::FETCH_OBJ);
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($phatTu));
    }

    public function phatTuSave() {
        $returnObj = array();
        $parsedBody = json_decode($this->app->request()->getBody());
        $this->app->logger->addInfo('--- Phat tu save request received : ' . json_encode($parsedBody));
        // validate
        if (!($parsedBody->name or $parsedBody->phap_danh)) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Name or Phap danh is required.';
        } else {
            if (0 > $parsedBody->id) {
                $insertStatement = $this->app->db->insert(array('name', 'phap_danh', 'phone', 'email',
                                                               'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                        ->into('phat_tu')
                                        ->values(array($parsedBody->name,
                                                       $parsedBody->phap_danh,
                                                       $parsedBody->phone,
                                                       $parsedBody->email,
                                                       $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                $insertId = $insertStatement->execute(false);
                $returnObj['result'] = 'ok';
                $returnObj['id'] = $insertId;
            } else {
                $updateStatement = $this->app->db->update(array('name' => $parsedBody->name,
                                                               'phap_danh' => $parsedBody->phap_danh,
                                                               'phone' => $parsedBody->phone,
                                                               'email' => $parsedBody->email))
                                        ->table('phat_tu')
                                        ->where('id', '=', $parsedBody->id);
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
