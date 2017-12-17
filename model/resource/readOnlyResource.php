<?php
namespace mafiascum\restApi\model\resource;

require_once('baseResource.php');

class ReadOnlyResource extends BaseResource {
    public function list($params) {
        $queryObj = array(
            'select' => $this->select_columns,
            'left_join' => $this->left_join_tables,
            'where' => array()
        );

        foreach ($this->query_columns as $column => $op) {
            if (array_key_exists($column, $params) && $params[$column]) {
                $queryObj['where'][$column] = array($op, $params[$column]);
            }
        }
        // $this->paginate_query($queryObj, $request);
        $sql = $this->generate_select_sql($queryObj);
        $result = $this->db->sql_query($sql);
        $response = $this->paginate_results($result, $params);
        return $response;
    }

    public function retrieve($id, $params) {
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
        if ($row) {
            $this->modify_read_row($row);
            return $row;
        } else {
            return null;
        }
    }

    public function to_json($data) {
        return $data;
    }
}
?>