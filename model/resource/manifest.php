<?php

namespace mafiascum\restApi\model\resource;

require_once(dirname(__FILE__) . "/v1/topicPosts.php");

use mafiascum\restApi\model\resource\ReadOnlyResource;
use mafiascum\restApi\model\resource\TopicPostsV1Resource;

class ResourceManifest {
    public static $resources = array(
        "topics" => array(
            "impl" => ReadOnlyResource::class,
            "table" => array(
                "from" => "topics",
                "alias" => "t",
            ),
            "primary_key_column" => "topic_id",
            "select_columns" => array("t.forum_id", "t.topic_title", "t.topic_poster, u.username"),
            "left_join_tables" => array(
                array("from" => "users u",
                      "on" => "t.topic_poster = u.user_id"),
            ),
            "query_columns" => array(),
            "permission_scopes" => array("f" => "forum_id"),
            "subresources" => array(
                "posts" => array(
                    "impl" => TopicPostsV1Resource::class,
                    "table" => array(
                        "from" => "posts",
                        "alias" => "p",
                    ),
                    "primary_key_column" => "post_id",
                    "select_columns" => array("p.topic_id", "p.poster_id", "u.username", "p.forum_id", "p.post_text", "p.bbcode_uid", "p.bbcode_bitfield"),
                    "left_join_tables" =>  array(
                        array("from" => "users u",
                              "on" => "p.poster_id = u.user_id"),
                    ),
                    "query_columns" => array( "poster_id" => "equals",
                                              "username" => "ilike",),
                    "permission_scopes" => array("f" => "forum_id"),
                    "subresources" => array(),
                )
            ),
        )
    );                       
}
?>