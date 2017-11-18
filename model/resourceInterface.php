<?php

namespace mafiascum\restApi\model;

interface IResource {
    public function has_permission($ids, $operation);
    
    public function list($request);

    public function create($data);
    
    public function retrieve($id, $request);

    public function update($id, $data);

    public function delete($id);

    public function sub_list($parent_id, $resource_name, $request);

    public function sub_retrieve($parent_id, $resource_name, $id);
}
?>