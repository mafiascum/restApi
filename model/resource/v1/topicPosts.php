<?php
namespace mafiascum\restApi\model\resource;

class TopicPostsV1Resource extends ReadOnlyResource {
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
}
?>