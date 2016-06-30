<?php
use Interop\Container\ContainerInterface;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class PhatTuController {
    protected $ci;

    public function __construct(ContainerInterface $ci) {
        $this->ci = $ci;
    }

    public function phatTuList($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $statement = $this->ci->db->select()->from('phat_tu')
                          ->limit($parsedBody['rowCount'],
                                  $parsedBody['rowCount']*($parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
                $statement->orderBy($key, $direction);
        $countStatement = $this->ci->db->select(array('id'))->count()->from('phat_tu');
        // filter by keyword
        if (!empty($parsedBody['searchPhrase'])) {
            $statement->where("CONCAT(id,'#',CONCAT_WS(',',name,phap_danh,phone,email))", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
            $countStatement->where("CONCAT(id,'#',CONCAT_WS(',',id,name,phap_danh,phone,email))", 'LIKE', '%' . $parsedBody['searchPhrase'] . '%');
        }
        // TODO filter by permission
        // filter with form
        if (!empty($parsedBody['searchForm'])) {
            if (!empty($parsedBody['searchForm']['name'])) {
                $statement->where('name', 'LIKE', '%' . $parsedBody['searchForm']['name'] . '%');
                $countStatement->where('name', 'LIKE', '%' . $parsedBody['searchForm']['name'] . '%');
            }
            if (!empty($parsedBody['searchForm']['phap_danh'])) {
                $statement->where('phap_danh', 'LIKE', '%' . $parsedBody['searchForm']['phap_danh'] . '%');
                $countStatement->where('phap_danh', 'LIKE', '%' . $parsedBody['searchForm']['phap_danh'] . '%');
            }
            if (!empty($parsedBody['searchForm']['email'])) {
                $statement->where('email', 'LIKE', '%' . $parsedBody['searchForm']['email'] . '%');
                $countStatement->where('email', 'LIKE', '%' . $parsedBody['searchForm']['email'] . '%');
            }
            if (!empty($parsedBody['searchForm']['phone'])) {
                $statement->where('phone', 'LIKE', '%' . $parsedBody['searchForm']['phone'] . '%');
                $countStatement->where('phone', 'LIKE', '%' . $parsedBody['searchForm']['phone'] . '%');
            }
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

    public function phatTuCreate($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Phat tu create request received : ' . json_encode($parsedBody));
        // validate
        if (!($parsedBody['name'] or $parsedBody['phap_danh'])) {
            $returnObj['result'] = 'error';
            $returnObj['message'] = 'Name or Phap danh is required.';
        } else {
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
        }
        return $response->withJson($returnObj);
    }

    public function phatTuDelete($request, $response, $args) {
        $returnObj = array();
        $parsedBody = $request->getParsedBody();
        $this->ci->logger->addInfo('--- Phat tu delete request received : ' . json_encode($parsedBody));
        // TODO validate
        $deleteStatement = $this->ci->db->delete()
                                ->from('phat_tu')
                                ->where('id','=',$parsedBody['id']);
        $returnObj['rows'] = $deleteStatement->execute();
        return $response->withJson($returnObj);
    }

    public function phatTuGet($request, $response, $args) {
        $parsedBody = $request->getParsedBody();
        $statement = $this->ci->db->select()->from('phat_tu')
                          ->where('id', '=', $parsedBody['id']);
        $pdoStatement = $statement->execute();
        $phatTu = $pdoStatement->fetch(PDO::FETCH_OBJ);
        return $response->withJson($phatTu);
    }

    public function phatTuSave($request, $response, $args) {
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
