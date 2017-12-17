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
            $columns[] = $column;
            $values[] = $value;
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
            $updates[] = $column . " = '" . $value . "'";
        }
        $sql = $sql . join($updates, ",");
        $sql = $sql . " WHERE 1=1 ";
        $sql = $sql . " AND " . $this->primary_key_column . " = " . $id;

        $result = $this->db->sql_query($sql);

        return $this->retrieve($id, array());
    }
}