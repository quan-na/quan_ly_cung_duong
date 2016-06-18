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
        $this->ci->logger->addInfo('--- Phat tu list request received : ' . json_encode($parsedBody));
        $statement = $this->ci->db->select()->from('phat_tu')
                          ->limit($parsedBody['rowCount'],
                                  $parsedBody['rowCount']*($parsedBody['current'] - 1));
        if (!empty($parsedBody['sort']))
            foreach ($parsedBody['sort'] as $key => $direction)
                $statement->orderBy($key, $direction);
        $countStatement = $this->ci->db->select(array('id'))->count()->from('phat_tu');
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
}
?>
