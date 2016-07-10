<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CungDuongController {
    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function cungDuongList($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Cung duong list request received : ' . json_encode($parsedBody));
        $statement = $this->ci->db->select(array('cung_duong.id',
                                                 'phat_tu.name as phat_tu_name','phap_danh as phat_tu_phap_danh','phone as phat_tu_phone','email as phat_tu_email',
                                                 'muc_cung_duong.name as muc_cung_duong',
                                                 "DATE_FORMAT(date,'%d/%m/%Y') AS date",'tinh_tai_vat','qui_doi','ghi_chu'))
                          ->from('cung_duong')
                          ->join('phat_tu', 'cung_duong.phat_tu_id', '=', 'phat_tu.id')
                          ->join('muc_cung_duong', 'cung_duong.muc_cung_duong_id', '=', 'muc_cung_duong.id')
                          ->limit((int)$parsedBody['rowCount'],
                                  (int)$parsedBody['rowCount']*((int)$parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
        $statement->orderBy($key, $direction);
        $countStatement = $this->ci->db->select(array('COUNT(cung_duong.id)'))->from('cung_duong')
                               ->join('phat_tu', 'cung_duong.phat_tu_id', '=', 'phat_tu.id')
                               ->join('muc_cung_duong', 'cung_duong.muc_cung_duong_id', '=', 'muc_cung_duong.id');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            // parse search phrase
            $statement->where("CONCAT('#',cung_duong.id,'#',CONCAT_WS(',',phat_tu.name,phap_danh,phone,email,muc_cung_duong.name, DATE_FORMAT(date,'%d/%m/%Y'),tinh_tai_vat,qui_doi,ghi_chu))", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
            $countStatement->where("CONCAT('#',cung_duong.id,'#',CONCAT_WS(',',phat_tu.name,phap_danh,phone,email,muc_cung_duong.name, DATE_FORMAT(date,'%d/%m/%Y'),tinh_tai_vat,qui_doi,ghi_chu))", 'LIKE', '%' . str_replace(' ', '%', $parsedBody['searchPhrase']) . '%');
        }
        // TODO filter by permission
        $pdoStatement = $statement->execute();
        $countPdoStatement = $countStatement->execute();
        $phatTuArray = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        $total = $countPdoStatement->fetch(PDO::FETCH_NUM);
        $returnObj['current'] = $parsedBody['current'];
        $returnObj['rowCount'] = $parsedBody['rowCount'];
        $returnObj['total'] = $total[0];
        $returnObj['rows'] = $phatTuArray;
        return $response->withJson($returnObj);
    }

    public function cungDuongDelete($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Cung duong delete request received : ' . json_encode($parsedBody));
        // TODO validate
        $deleteStatement = $this->ci->db->delete()
                                ->from('cung_duong')
                                ->where('id','=',$parsedBody['id']);
        $returnObj['rows'] = $deleteStatement->execute();
        return $response->withJson($returnObj);
    }

    public function cungDuongGet($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $statement = $this->ci->db->select()->from('cung_duong')
                          ->where('id', '=', $parsedBody['id']);
        $pdoStatement = $statement->execute();
        $phatTu = $pdoStatement->fetch(PDO::FETCH_OBJ);
        return $response->withJson($phatTu);
    }

    public function cungDuongSave($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Cung duong save request received : ' . json_encode($parsedBody));
        // validate
        if (!$parsedBody['tinh_tai_vat'] or !$parsedBody['qui_doi'] or !$parsedBody['date'] or $parsedBody['phat_tu_id'] == '-1') {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Required fields are missing.';
        } else {
            if (0 > $parsedBody['id']) {
                // check muc cung duong
                if (!ctype_digit($parsedBody['muc_cung_duong_id'])) {
                    $mcdInsertStmt = $this->ci->db->insert(array('name',
                                                                 'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                          ->into('muc_cung_duong')
                                          ->values(array($parsedBody['muc_cung_duong_id'],
                                                         $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                    $mcdInsertedId = $mcdInsertStmt->execute(true);
                    $parsedBody['muc_cung_duong_id'] = $mcdInsertedId;
                }
                // parse date
                $parsedBody['date'] = \DateTime::createFromFormat('d/m/Y', $parsedBody['date'])->format('Y-m-d');
                $insertStatement = $this->ci->db->insert(array('phat_tu_id', 'muc_cung_duong_id', 'date', 'tinh_tai_vat', 'qui_doi', 'ghi_chu',
                                                               'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                        ->into('cung_duong')
                                        ->values(array($parsedBody['phat_tu_id'],
                                                       $parsedBody['muc_cung_duong_id'],
                                                       $parsedBody['date'],
                                                       $parsedBody['tinh_tai_vat'],
                                                       $parsedBody['qui_doi'],
                                                       $parsedBody['ghi_chu'],
                                                       $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                $insertId = $insertStatement->execute(false);
                $returnObj['result'] = 'ok';
                $returnObj['id'] = $insertId;
            } else {
                // check muc cung duong
                if (!ctype_digit($parsedBody['muc_cung_duong_id'])) {
                    $mcdInsertStmt = $this->ci->db->insert(array('name',
                                                                 'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                          ->into('muc_cung_duong')
                                          ->values(array($parsedBody['muc_cung_duong_id'],
                                                         $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                    $mcdInsertedId = $mcdInsertStmt->execute(true);
                    $parsedBody['muc_cung_duong_id'] = $mcdInsertedId;
                }
                // parse date
                $parsedBody['date'] = \DateTime::createFromFormat('d/m/Y', $parsedBody['date'])->format('Y-m-d');
                $updateStatement = $this->ci->db->update(array('phat_tu_id' => $parsedBody['phat_tu_id'],
                                                               'muc_cung_duong_id' => $parsedBody['muc_cung_duong_id'],
                                                               'date' => $parsedBody['date'],
                                                               'tinh_tai_vat' => $parsedBody['tinh_tai_vat'],
                                                               'qui_doi' => $parsedBody['qui_doi'],
                                                               'ghi_chu' => $parsedBody['ghi_chu']))
                                        ->table('cung_duong')
                                        ->where('id', '=', $parsedBody['id']);
                $rows = $updateStatement->execute();
                $returnObj['result'] = 'ok';
                $returnObj['rows'] = $rows;
            }
        }
        return $response->withJson($returnObj);
    }

    public function cungDuongReport($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Cung duong report request received : ' . json_encode($parsedBody));
        // get selecting fields
        $selectingFields = array("SUM(cd.qui_doi) AS sumCungDuong");
        $joinPhatTu = false;
        $joinMucCungDuong = false;
        switch ($parsedBody['groupBy_date']) {
            case 'day':
                array_push($selectingFields, "DATE_FORMAT(cd.date,'%d/%m/%Y') AS day");
                break;
            case 'month':
                array_push($selectingFields, "DATE_FORMAT(cd.date,'%m/%Y') AS month");
                break;
            case 'year':
                array_push($selectingFields, "DATE_FORMAT(cd.date,'%Y') AS year");
                break;
        }
        if ($parsedBody['groupBy'] and in_array('phatTu', $parsedBody['groupBy'])) {
            array_push($selectingFields, "pt.name AS name", "pt.phap_danh AS phapDanh", "pt.email AS email");
            $joinPhatTu = true;
        }
        if ($parsedBody['groupBy'] and in_array('mucCungDuong', $parsedBody['groupBy'])) {
            array_push($selectingFields, "mcd.name AS mucCungDuong");
            $joinMucCungDuong = true;
        }
        $statement = $this->ci->db->select($selectingFields)->from("cung_duong cd");
        if ($joinPhatTu)
            $statement->join("phat_tu pt", "pt.id", "=", "cd.phat_tu_id");
        if ($joinMucCungDuong)
            $statement->join("muc_cung_duong mcd", "mcd.id", "=", "cd.muc_cung_duong_id");
        // generate where
        if ($parsedBody['fromDate']) {
            $parsedBody['fromDate'] = \DateTime::createFromFormat('d/m/Y', $parsedBody['fromDate'])->format('Y-m-d');
            $statement->where('cd.date', '>=', $parsedBody['fromDate']);
        }
        if ($parsedBody['toDate']) {
            $parsedBody['toDate'] = \DateTime::createFromFormat('d/m/Y', $parsedBody['toDate'])->format('Y-m-d');
            $statement->where('cd.date', '<=', $parsedBody['toDate']);
        }
        if ($parsedBody['muc_cung_duong_id']) {
            $statement->whereIn('cd.muc_cung_duong_id', $parsedBody['muc_cung_duong_id']);
        }
        if ($parsedBody['minQuiDoi']) {
            $statement->where('cd.qui_doi', '>=', $parsedBody['minQuiDoi']);
        }
        // generate group by
        $groupByStr = "";
        switch ($parsedBody['groupBy_date']) {
            case 'day':
                $groupByStr = $groupByStr . "DATE_FORMAT(cd.date,'%d/%m/%Y'),";
                $statement->orderBy("DATE_FORMAT(cd.date,'%Y-%m-%d')", "ASC");
                break;
            case 'month':
                $groupByStr = $groupByStr . "DATE_FORMAT(cd.date,'%m/%Y'),";
                $statement->orderBy("DATE_FORMAT(cd.date,'%Y-%m')", "ASC");
                break;
            case 'year':
                $groupByStr = $groupByStr . "DATE_FORMAT(cd.date,'%Y'),";
                $statement->orderBy("DATE_FORMAT(cd.date,'%Y')", "ASC");
                break;
        }
        $statement->orderBy("SUM(cd.qui_doi)", "DESC");
        if ($parsedBody['groupBy'] and in_array('phatTu', $parsedBody['groupBy'])) {
            $groupByStr = $groupByStr . "cd.phat_tu_id,";
        }
        if ($parsedBody['groupBy'] and in_array('mucCungDuong', $parsedBody['groupBy'])) {
            $groupByStr = $groupByStr . "cd.muc_cung_duong_id,";
        }
        $groupByStr = rtrim($groupByStr, ',');
        if ($groupByStr)
            $statement->groupBy($groupByStr);
        $pdoStatement = $statement->execute();
        $rows = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        $returnObj['result'] = 'ok';
        $returnObj['rows'] = $rows;
        return $response->withJson($returnObj);
    }
}
?>
