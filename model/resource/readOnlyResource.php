<?php
namespace mafiascum\restApi\model\resource;

require_once('baseResource.php');

class ReadOnlyResource extends BaseResource {
    public function list($request) {
        $queryObj = array(
            'select' => $this->select_columns,
            'left_join' => $this->left_join_tables,
            'where' => array()
        );

        foreach ($this->query_columns as $column => $op) {
            $filterValue = $this->db->sql_escape($request->variable($column, ''));
            if ($filterValue) {
                $queryObj['where'][$column] = array($op, $filterValue);
            }
        }
        // $this->paginate_query($queryObj, $request);
        $sql = $this->generate_select_sql($queryObj);
        $result = $this->db->sql_query($sql);
        $response = $this->paginate_results($result, $request);
        return $response;
    }

    public function retrieve($id, $request) {
        if (empty($this->has_permission(array($id), "get"))) {
            return null;
        }

        $queryObj = array(
            'select' => $this->select_columns,
            'left_join' => $this->left_join_tables,
            'where' => array(
                $this->table["alias"] . "." . $this->primary_key_column => array("equals", $id)
            )
        );
        
        $sql = $this->generate_select_sql($queryObj);
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->modify_read_row($row);
        return $row;
    }

    public function to_json($data) {
        return $data;
    }
}
?>