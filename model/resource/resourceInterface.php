<?php

namespace mafiascum\restApi\model\resource;

interface IResource {
    public function has_permission($ids, $operation);
    
    public function list($params);

    public function create($data);
    
    public function retrieve($id, $params);

    public function update($id, $data);

    public function delete($id);

    public function to_json($data);

    public function from_json($jsonData);

    public function validate($id, $jsonData);
}
?>