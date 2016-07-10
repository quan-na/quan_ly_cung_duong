<?php

class MucCungDuongController {
    protected $app;

    public function __construct(\Slim\Slim $app) {
        $this->app = $app;
    }

    public function mucCungDuongList() {
        $returnObj = array();
        $parsedBody = $this->app->request->post();
        $statement = $this->app->db->select()->from('muc_cung_duong');
        if (isset($parsedBody['rowCount']) and isset($parsedBody['current']))
            $statement->limit((int)$parsedBody['rowCount'],
                              (int)$parsedBody['rowCount']*((int)$parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
        $statement->orderBy($key, $direction);
        $countStatement = $this->app->db->select(array('COUNT(id)'))->from('muc_cung_duong');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            $statement->where("CONCAT('#',id,'#',name)", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
            $countStatement->where("CONCAT('#',id,'#',name)", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
        }
        $pdoStatement = $statement->execute();
        $countPdoStatement = $countStatement->execute();
        $phatTuArray = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        $total = $countPdoStatement->fetch(PDO::FETCH_NUM);
        if (isset($parsedBody['rowCount']) and isset($parsedBody['current'])) {
            $returnObj['current'] = $parsedBody['current'];
            $returnObj['rowCount'] = $parsedBody['rowCount'];
        }
        $returnObj['total'] = $total[0];
        $returnObj['rows'] = $phatTuArray;
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($returnObj));
    }

    public function mucCungDuongDelete() {
        $returnObj = array();
        $parsedBody = json_decode($this->app->request()->getBody());
        $this->app->logger->addInfo('--- Muc cung duong delete request received : ' . json_encode($parsedBody));
        // TODO validate
        $deleteStatement = $this->app->db->delete()
                                ->from('muc_cung_duong')
                                ->where('id','=',$parsedBody->id);
        $returnObj['rows'] = $deleteStatement->execute();
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($returnObj));
    }

    public function mucCungDuongGet() {
        $parsedBody = json_decode($this->app->request()->getBody());
        $statement = $this->app->db->select()->from('muc_cung_duong')
                          ->where('id', '=', $parsedBody->id);
        $pdoStatement = $statement->execute();
        $phatTu = $pdoStatement->fetch(PDO::FETCH_OBJ);
        $this->app->response()->status(200);
        $this->app->response()->body(json_encode($phatTu));
    }

    public function mucCungDuongSave() {
        $returnObj = array();
        $parsedBody = json_decode($this->app->request()->getBody());
        $this->app->logger->addInfo('--- Muc cung duong save request received : ' . json_encode($parsedBody));
        // validate
        if (!$parsedBody->name) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Name is required.';
        } else {
            if (0 > $parsedBody->id) {
                $insertStatement = $this->app->db->insert(array('name',
                                                                'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                        ->into('muc_cung_duong')
                                        ->values(array($parsedBody->name,
                                                       $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                $insertId = $insertStatement->execute(false);
                $returnObj['result'] = 'ok';
                $returnObj['id'] = $insertId;
            } else {
                $updateStatement = $this->app->db->update(array('name' => $parsedBody->name))
                                        ->table('muc_cung_duong')
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
