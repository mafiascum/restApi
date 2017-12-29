<?
namespace mafiascum\restApi\model\resource;

use mafiascum\restApi\utils\DbUtils;

require_once('readOnlyResource.php');

class ReadWriteResource extends ReadOnlyResource {
    public function delete($id) {
        global $table_prefix;

        $sql = "";
        $sql = $sql . "DELETE FROM " . $table_prefix . $this->table["from"];
        $sql = $sql . " WHERE " . $this->primary_key_column . " = " . $id;

        $this->db->sql_query($sql);
    }

    public function create($data) {
        global $table_prefix;
        
        $sql = "";
        $sql = $sql . "INSERT INTO " . $table_prefix . $this->table["from"];
        $sql = $sql . " (";

        $columns = array();
        $values = array();
        foreach($data as $column => $value) {
            if (array_key_exists($column, $this->fields)) {
                $columns[] = $column;
                $values[] = $value;
            }
        }
        foreach($this->parent_record as $fk_column => $fk_value) {
            $columns[] = $fk_column;
            $values[] = $fk_value;
        }

        $sql = $sql . join($columns, ",");
        $sql = $sql . ") VALUES (";
        $sql = $sql . DbUtils::array_to_quoted_string($values);
        $sql = $sql . ")";

        $this->db->sql_query($sql);

        $sql = 'select last_insert_id() as id';
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $id = $row['id'];

        return $this->retrieve($id, array());
    }

    public function update($id, $data) {
        global $table_prefix;

        $sql = "";
        $sql = $sql . "UPDATE " . $table_prefix . $this->table["from"];
        $sql = $sql . " SET ";

        $updates = array();
        foreach($data as $column => $value) {
            if (array_key_exists($column, $this->fields)) {
                $updates[] = $column . " = '" . $value . "'";
            }
        }

        if (empty($updates)) {
            return $this->retrieve($id, array());
        }
        $sql = $sql . join($updates, ",");
        $sql = $sql . " WHERE 1=1 ";
        $sql = $sql . " AND " . $this->primary_key_column . " = " . $id;

        $result = $this->db->sql_query($sql);

        return $this->retrieve($id, array());
    }

    public function from_json($jsonData) {
        return $jsonData;
    }

    public function validate($id, $jsonData) {
        $errors = array();

        //do all stock field level validation as implemented
        $this->validate_required($errors, $id, $jsonData);

        //loop through custom validators as instance members
        foreach ($this->validators as $validator) {
            $this->{$validator}($errors, $id, $jsonData);
        }

        return $errors;
    }

    public function validate_required(&$errors, $id, $jsonData) {
        if (isset($id)) {
            $resource = $this->retrieve($id);
        } else {
            $resource = array();
        }

        foreach ($this->fields as $field => $validation) {
            if ($validation["required"] && !isset($resource[$field]) && (!isset($jsonData[$field]) || is_null($jsonData[$field]) || $jsonData[$field] == "")) {
                $errors[] = array(
                    "field" => $field,
                    "type" => "required",
                    "message" => $this->language->lang("VALIDATION_ERROR_REQUIRED", $field),
                );
            }
        }
    }
}