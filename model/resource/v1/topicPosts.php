<?php
namespace mafiascum\restApi\model\resource;

class TopicPostsV1Resource extends ReadOnlyResource {
    protected function modify_read_row(&$row) {
        $encoded_message = $row["post_text"];
        decode_message($encoded_message, $row["bbcode_uid"]);
        $row["post_bbcode"] = $encoded_message;
    }
}
?>