<?php
namespace mafiascum\restApi\api;

require_once(dirname(__FILE__) . "/../../model/resource/readWriteResource.php");

use mafiascum\restApi\model\resource\ReadWriteResource;

class TopicPostsV1Resource extends ReadWriteResource {
    public function to_json($data) {
        $jsonData = $data;
        $jsonData["post_time"] = date("c", $jsonData["post_time"]);
        return $jsonData;
    }

    protected function modify_read_row(&$row) {
        $encoded_message = $row["post_text"];
        decode_message($encoded_message, $row["bbcode_uid"]);
        $row["post_bbcode"] = $encoded_message;
    }

    protected function get_post_number_subquery() {
        global $table_prefix;
        $sql = "(";
        $sql = $sql . "SELECT post_id, row_number() OVER (ORDER BY post_time, post_id) - 1 post_number";
        $sql = $sql . " FROM " . $table_prefix . "posts";
        $sql = $sql . " WHERE topic_id = " . $this->parent_record["topic_id"];
        $sql = $sql . ")";
        return $sql;
    }
}
?>