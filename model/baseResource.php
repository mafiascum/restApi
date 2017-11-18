<?php

namespace mafiascum\restApi\model;

require_once('resourceInterface.php');

abstract class BaseResource implements IResource {
    protected $db;

    protected $auth;

    protected $table;

    protected $primary_key_column;

    protected $select_columns;

    protected $left_join_tables;

    protected $query_columns;

    protected $permission_scopes;

    protected $sub_resources;

    protected $parent_record;

    protected function create_from_spec($db, $auth, $table, $primary_key_column, $select_columns, $left_join_tables, $query_columns, $permission_scopes, $sub_resources, $parent_record)
    {
        $this->db = $db;
        $this->auth = $auth;
        $this->table = $table;
        $this->primary_key_column = $primary_key_column;
        $this->select_columns = $select_columns;
        $this->left_join_tables = $left_join_tables;
        $this->query_columns = $query_columns;
        $this->permission_scopes = $permission_scopes;
        $this->sub_resources = $sub_resources;
        $this->parent_record = $parent_record;
    }

    protected function generate_sql($queryObj) {
        $sql = "";
        $sql = $sql . "SELECT " . $this->primary_key_column;
        
        foreach ($queryObj['select'] as $alias => $column) {
            if (is_numeric($alias)) {
                $sql = $sql . ", " . $column;
            } else {
                $sql = $sql . ", " . $column . " as " . $alias;
            }
        }
        
        $sql = $sql . " FROM " . $this->table["table"] . " " . $this->table["alias"];

        foreach ($this->left_join_tables as $left_join_table) {
            $sql = $sql . " LEFT JOIN " . $left_join_table["from"];
            $sql = $sql . " ON " . $left_join_table["on"];
        }
        
        $sql = $sql . " WHERE 1 = 1 ";

        foreach ($this->parent_record as $fk_column => $fk_value) {
            $sql = $sql . " AND " . $this->table["alias"] . "." . $fk_column . " = " . $fk_value;
        }

        foreach ($queryObj['where'] as $column => $condition) {
            switch ($condition[0]) {
            case 'in':
                $column = $column;
                $op = " IN ";
                $value = "(" .  $this->array_to_quoted_string($condition[1]) . ")";
                break;
            case 'equals':
                $column = $column;
                $op = "=";
                $value = "'" . $condition[1] . "'";
                break;
            case 'iequals':
                $column = "lower(" . $column . ")";
                $op = "=";
                $value = "lower('" . $condition[1] . "')";
                break;
            case 'like':
                $column = $column;
                $op = " LIKE ";
                $value = "'%" . $condition[1] . "%'";
                break;
                
            case 'ilike':
                $column = "lower(" . $column . ")";
                $op = " LIKE ";
                $value = "lower('%" . $condition[1] . "%')";
                break;
            }
            $sql = $sql . " AND " . $column . $op . $value;
        }

        if (array_key_exists('order', $queryObj)) {
            if (is_array($queryObj['order'])) {
                $order_column = $queryObj['order'][0];
                $order_direction = $queryObj['order'][1] ?: 'ASC';
            } else {
                $order_column = $queryObj['order'];
                $order_direction = 'ASC';
            }
                
            $sql = $sql . " ORDER BY " . $order_column . " " . $order_direction;
        }
        return $sql;
    }

    protected function paginate_results($result, $request) {
        $limit = $request->variable('limit', 50);
        $start = $request->variable('start', 0);

        $unfiltered_data = array();
        while ($row = $this->db->sql_fetchrow($result)) {
            $unfiltered_data[] = $row;
        }
        $permitted_ids = $this->has_permission(array_column($unfiltered_data, $this->primary_key_column), 'get');
        $filtered_data = array_values(array_filter($unfiltered_data, function ($item) use ($permitted_ids) {
            return in_array($item[$this->primary_key_column], $permitted_ids);
        }));
        $total = count($filtered_data);
        $data = array_slice($filtered_data, $start, $limit);
        foreach ($data as $i => $row) {
            $this->modify_read_row($row);
            $data[$i] = $row;
        }
        $response = array(
            "data" => $data,
            "total" => $total,
            "next" => ($start >= $total - 1 || $total == 0) ? null : max(0, min($start + $limit, $total - 1)),
            "prev" => ($start == 0 || $total == 0) ? null : max(0, min($start - $limit, $total - 1)),
        );
        return $response;
    }

    protected function array_to_quoted_string($arr) {
        return '\'' . join( '\', \'', $arr ) . '\'';
    }

    public function has_permission($ids, $operation) {
        if ($operation == 'get') {
            $queryObj = array(
                'select' => array(),
                'from' => $this->table,
                'where' => array(
                    $this->primary_key_column => array("in", $ids)
                ),
            );
            
            foreach ($this->permission_scopes as $type => $column) {
                $queryObj['select'][] = $column;
            }
            
            $sql = $this->generate_sql($queryObj);
            $result = $this->db->sql_query($sql);
            $permitted = $ids;
            while ($row = $this->db->sql_fetchrow($result)) {
                foreach ($this->permission_scopes as $type => $column) {
                    if ($row[$column] == null || !$this->auth->acl_get($type . '_read', $row[$column])) {
                        if (($key = array_search($row[$this->primary_key_column], $permitted)) !== false) {
                            unset($permitted[$key]);
                        }
                    }
                }
            }
            return $permitted;
        } else {
            //todo
            return array();
        }
    }

    protected function modify_read_row(&$row) {
        // no-op
    }
    
    public function list($request) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function create($data) {
        throw new \BadMethodCallException("Not Implemented");
    }
    
    public function retrieve($id, $request) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function update($id, $data) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function delete($id) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function sub_list($parent_id, $resource_name, $request) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function sub_retrieve($parent_id, $resource_name, $id) {
        throw new \BadMethodCallException("Not Implemented");
    }
}
?>