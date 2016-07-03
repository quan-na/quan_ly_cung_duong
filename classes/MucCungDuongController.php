<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class MucCungDuongController {
    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function mucCungDuongList($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $statement = $this->ci->db->select()->from('muc_cung_duong')
                          ->limit($parsedBody['rowCount'],
                                  $parsedBody['rowCount']*($parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
        $statement->orderBy($key, $direction);
        $countStatement = $this->ci->db->select(array('id'))->count()->from('muc_cung_duong');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            $statement->where("CONCAT(id,'#',name)", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
            $countStatement->where("CONCAT(id,'#',name)", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
        }
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

    public function mucCungDuongDelete($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Muc cung duong delete request received : ' . json_encode($parsedBody));
        // TODO validate
        $deleteStatement = $this->ci->db->delete()
                                ->from('muc_cung_duong')
                                ->where('id','=',$parsedBody['id']);
        $returnObj['rows'] = $deleteStatement->execute();
        return $response->withJson($returnObj);
    }

    public function mucCungDuongGet($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $statement = $this->ci->db->select()->from('muc_cung_duong')
                          ->where('id', '=', $parsedBody['id']);
        $pdoStatement = $statement->execute();
        $phatTu = $pdoStatement->fetch(PDO::FETCH_OBJ);
        return $response->withJson($phatTu);
    }

    public function mucCungDuongSave($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Muc cung duong save request received : ' . json_encode($parsedBody));
        // validate
        if (!$parsedBody['name']) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Name is required.';
        } else {
            if (0 > $parsedBody['id']) {
                $insertStatement = $this->ci->db->insert(array('name',
                                                               'ar_owner', 'ar_group_level', 'ar_user', 'ar_group', 'ar_other'))
                                        ->into('muc_cung_duong')
                                        ->values(array($parsedBody['name'],
                                                       $_SESSION['username'], $_SESSION['group_level'], 3, 2, 0));
                $insertId = $insertStatement->execute(false);
                $returnObj['result'] = 'ok';
                $returnObj['id'] = $insertId;
            } else {
                $updateStatement = $this->ci->db->update(array('name' => $parsedBody['name']))
                                        ->table('muc_cung_duong')
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
