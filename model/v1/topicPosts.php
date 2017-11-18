<?php
namespace mafiascum\restApi\model;

class TopicPostsV1Resource extends ReadOnlyResource {
    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth, $parent_record, $parent_id) {
        global $table_prefix;
        $this->create_from_spec(
            $db,
            $auth,
            array("table" => $table_prefix . "posts",
                  "alias" => "p"),
            "post_id",
            array("p.topic_id", "p.poster_id", "u.username", "p.forum_id", "p.post_text", "p.bbcode_uid", "p.bbcode_bitfield"),
            array(
                array("from" => $table_prefix . "users u",
                      "on" => "p.poster_id = u.user_id"),
            ),
            array(
                "poster_id" => "equals",
                "username" => "ilike",
            ),
            array("f" => "forum_id"),
            array(),
            array_merge(
                $parent_record,
                array("topic_id" => $parent_id)
            )
        );
    }

    protected function modify_read_row(&$row) {
        $encoded_message = $row["post_text"];
        decode_message($encoded_message, $row["bbcode_uid"]);
        $row["post_bbcode"] = $encoded_message;
    }
}