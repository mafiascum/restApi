<?php

namespace mafiascum\restApi\model\resource;

require_once('resourceInterface.php');
require_once(dirname(__FILE__) . "/../../utils/db.php");

use mafiascum\restApi\utils\DbUtils;

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

    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, $spec, $parent_record) {
        $this->db = $db;
        $this->auth = $auth;

        $this->table = $spec["table"];
        $this->select_columns = $spec["select_columns"];
        $this->primary_key_column = $spec["primary_key_column"];
        $this->left_join_tables = $spec["left_join_tables"];
        $this->query_columns = $spec["query_columns"];
        $this->permission_scopes = $spec["permission_scopes"];
        $this->subresources = $spec["subresources"];

        $this->parent_record = $parent_record;
    }

    protected function generate_select_sql($queryObj) {
        global $table_prefix;

        $sql = "";
        $sql = $sql . "SELECT " . $this->table["alias"] . "." . $this->primary_key_column;
        
        foreach ($queryObj['select'] as $alias => $column) {
            if (is_numeric($alias)) {
                $sql = $sql . ", " . $column;
            } else {
                $sql = $sql . ", " . $column . " as " . $alias;
            }
        }
        
        $sql = $sql . " FROM " . $table_prefix . $this->table["from"] . " " . $this->table["alias"];

        if (array_key_exists("left_join", $queryObj)) {
            foreach ($queryObj["left_join"] as $left_join_table) {
                preg_match("/func:(\w+):/", $left_join_table["from"], $matches);

                if (array_key_exists(1, $matches)) {
                    $sql = $sql . " LEFT JOIN " . preg_replace("/func:(\w+):/", $this->{$matches[1]}(), $left_join_table["from"]);
                } else {
                    $sql = $sql . " LEFT JOIN " . $table_prefix . $left_join_table["from"];
                }
                $sql = $sql . " ON " . $left_join_table["on"];
            }
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
                $value = "(" .  DbUtils::array_to_quoted_string($condition[1]) . ")";
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
            case 'lt':
                $column = $colmn;
                $op = "<";
                $value = $condition[1];
                break;
            case 'lte':
                $column = $column;
                $op = "<=";
                $value = $condition[1];
                break;
            case 'gt':
                $column = $column;
                $op = ">";
                $value = $condition[1];
                break;
            case 'gte':
                $column = $column;
                $op = ">=";
                $value = $condition[1];
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
        } else {
            $sql = $sql . " ORDER BY " . $this->table["alias"] . "." . $this->primary_key_column;
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
            "next" => ($start + $limit >= $total || $total == 0) ? null : max(0, min($start + $limit, $total - 1)),
            "prev" => ($start == 0 || $total == 0) ? null : max(0, min($start - $limit, $total - 1)),
        );
        return $response;
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
            
            $sql = $this->generate_select_sql($queryObj);
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

    public function get_primary_key_column() {
        return $this->primary_key_column;
    }

    public function get_subresource_def($resource_name) {
        return $this->subresources[$resource_name];
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

    public function to_json($data) {
        throw new \BadMethodCallException("Not Implemented");
    }

    public function from_json($jsonData) {
        throw new \BadMethodCallException("Not Implemented");
    }
}
?>