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
                                                 'date','tinh_tai_vat','qui_doi','ghi_chu'))
                          ->from('cung_duong')
                          ->join('phat_tu', 'cung_duong.phat_tu_id', '=', 'phat_tu.id')
                          ->join('muc_cung_duong', 'cung_duong.muc_cung_duong_id', '=', 'muc_cung_duong.id')
                          ->limit($parsedBody['rowCount'],
                                  $parsedBody['rowCount']*($parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
        $statement->orderBy($key, $direction);
        $countStatement = $this->ci->db->select(array('cung_duong.id'))->count()->from('cung_duong')
                               ->join('phat_tu', 'cung_duong.phat_tu_id', '=', 'phat_tu.id')
                               ->join('muc_cung_duong', 'cung_duong.muc_cung_duong_id', '=', 'muc_cung_duong.id');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            $statement->where("CONCAT(cung_duong.id,'#',CONCAT_WS(',',phat_tu.name,phap_danh,phone,email,muc_cung_duong.name, date,tinh_tai_vat,qui_doi,ghi_chu))", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
            $countStatement->where("CONCAT(cung_duong.id,'#',CONCAT_WS(',',phat_tu.name,phap_danh,phone,email,muc_cung_duong.name, date,tinh_tai_vat,qui_doi,ghi_chu))", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
        }
        // TODO filter by permission
        $pdoStatement = $statement->execute();
        $countPdoStatement = $countStatement->execute();
        $phatTuArray = $pdoStatement->fetchAll(PDO::FETCH_OBJ);
        $total = $countPdoStatement->fetch(PDO::FETCH_NUM);
        $returnObj['current'] = $parsedBody['current'];
        $returnObj['rowCount'] = $parsedBody['rowCount'];
        $returnObj['total'] = $total[1];
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
        $this->ci->logger->addInfo('--- Phat tu save request received : ' . json_encode($parsedBody));
        // validate
        if (!($parsedBody['name'] or $parsedBody['phap_danh'])) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Name or Phap danh is required.';
        } else {
            if (0 > $parsedBody['id']) {
                $insertStatement = $this->ci->db->insert(array('name', 'phap_danh', 'phone', 'email',
                                                               'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                        ->into('phat_tu')
                                        ->values(array($parsedBody['name'],
                                                       $parsedBody['phap_danh'],
                                                       $parsedBody['phone'],
                                                       $parsedBody['email'],
                                                       $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                $insertId = $insertStatement->execute(false);
                $returnObj['result'] = 'ok';
                $returnObj['id'] = $insertId;
            } else {
                $updateStatement = $this->ci->db->update(array('name' => $parsedBody['name'],
                                                               'phap_danh' => $parsedBody['phap_danh'],
                                                               'phone' => $parsedBody['phone'],
                                                               'email' => $parsedBody['email']))
                                        ->table('phat_tu')
                                        ->where('id', '=', $parsedBody['id']);
                $rows = $updateStatement->execute();
                $returnObj['result'] = 'ok';
                $returnObj['rows'] = $rows;
            }
        }
        return $response->withJson($returnObj);
    }
}
?>
