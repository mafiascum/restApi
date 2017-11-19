<?php
namespace mafiascum\restApi\model\resource;

require_once('topicPosts.php');

class TopicsV1Resource extends ReadOnlyResource {
    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\auth\auth $auth) {
        global $table_prefix;
        $this->create_from_spec(
            $db,
            $auth,
            array("table" => $table_prefix . "topics",
                  "alias" => "t"),
            "topic_id",
            array("t.forum_id", "t.topic_title", "t.topic_poster, u.username"),
            array(
                array("from" => $table_prefix . "users u",
                      "on" => "t.topic_poster = u.user_id"),
            ),
            array(),
            array("f" => "forum_id"),
            array("posts" => TopicPostsV1Resource::class),
            array()
        );
    }
}
