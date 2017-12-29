<?php

namespace mafiascum\restApi\test\api;

require_once(dirname(__FILE__) . "/../../model/resource/resourceFactory.php");
require_once(dirname(__FILE__) . "/../../api/routes.php");

use mafiascum\restApi\model\resource\ResourceFactory;
use mafiascum\restApi\api\Routes;

class ResourceTest extends \phpbb_database_test_case {

    /** @var mafiascum\restApi\model\resource */
    protected $routes;

    protected $db;

    protected $auth;

    public function getDataSet()
    {
        return $this->createXMLDataSet(dirname(__FILE__) . '/fixtures/api.xml');
    }


    public function setUp()
    {
        parent::setUp();

        global $phpbb_dispatcher;
		$phpbb_dispatcher = new \phpbb_mock_event_dispatcher;

        $this->db = $this->new_dbal();
        
        $this->auth = $this->getMockBuilder('\phpbb\auth\auth')
                    ->disableOriginalConstructor()
                    ->getMock();

        $this->language = $this->getMockBuilder('\phpbb\language\language')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->routes = new ResourceFactory(Routes::$routes);
    }

    public function test_list() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array()
        );
        $this->assertEquals(9, count($response['data']));
        $this->assertEquals(9, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array("limit" => 2)
        );

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals(9, $response['total']);
        $this->assertEquals(2, $response['next']);
        $this->assertEquals(null, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array("limit" => 2, "start" => 3)
        );

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals(9, $response['total']);
        $this->assertEquals(5, $response['next']);
        $this->assertEquals(1, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array("limit" => 2, "start" => 8)
        );

        $this->assertEquals(1, count($response['data']));
        $this->assertEquals(9, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(6, $response['prev']);
    }

    public function test_retrieve() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $response = $this->routes->retrieve_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array("topic_id" => 1),
            array()
        );

        $this->assertEquals(array(
            "topic_id" => "1",
            "forum_id" => "1",
            "topic_title" => "aaaaa",
            "topic_poster" => "1001",
            "username" => "tom",
        ), $response);
    }

    public function test_sub_list() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1),
            array()
        );

        $this->assertEquals(3, count($response['data']));
        $this->assertEquals(3, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1),
            array("poster_id" => 1001)
        );

        $this->assertEquals(1, count($response['data']));
        $this->assertEquals(1, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1),
            array("username" => "tom")
        );

        $this->assertEquals(1, count($response['data']));
        $this->assertEquals(1, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);

        $response = $this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1),
            array("post_number" => "1")
        );

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals(2, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);
    }

    public function test_create() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $result = $this->routes->create_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array(
                "forum_id" => 1,
                "topic_title" => "bonesaw",
                "topic_poster" => 1001,
            )
        );

        $this->assertEquals("1", $result["forum_id"]);
        $this->assertEquals("bonesaw", $result["topic_title"]);
        $this->assertEquals("1001", $result["topic_poster"]);
        $this->assertEquals("tom", $result["username"]);
    }

    public function test_update() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $result = $this->routes->update_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array("topic_id" => 1),
            array(
                "topic_poster" => 1002,
            )
        );

        $this->assertEquals("1", $result["forum_id"]);
        $this->assertEquals("1", $result["topic_id"]);
        $this->assertEquals("aaaaa", $result["topic_title"]);
        $this->assertEquals("1002", $result["topic_poster"]);
        $this->assertEquals("rick", $result["username"]);
    }

    public function test_delete() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $this->routes->delete_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array("topic_id" => 1)
        );

        $response = $this->routes->retrieve_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array("topic_id" => 1),
            array()
        );

        $this->assertEquals(null, $response);
    }

    public function test_validation() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $this->language
            ->method("lang")
            ->with("VALIDATION_ERROR_REQUIRED", "topic_title")
            ->willReturn("topic_title is a required field.");
        
        $response = $this->routes->create_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            array(
                "forum_id" => 1,
                "topic_poster" => 1001,
            ),
            true
        );

        $this->assertEquals(array(
            "status" => 400,
            "errors" => array(
                array(
                    "field" => "topic_title",
                    "type" => "required",
                    "message" => "topic_title is a required field.",
                ),
            ),
        ), $response);
    }

    public function test_serialization() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $response = $this->routes->retrieve_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1, "post_id" => 1),
            array(),
            true
        );

        $this->assertEquals(array(
            "topic_id"        => "1",
            "post_id"         => "1",
            "poster_id"       => "1001",
            "username"        => "tom",
            "forum_id"        => "1",
            "post_text"       => "test",
            "bbcode_uid"      => "aaaaaaaa",
            "bbcode_bitfield" => "",
            "post_time"       => "2017-12-29T21:49:13+00:00",
            "post_number"     => "0",
            "post_bbcode"     => "test",
        ), $response);
    }

    public function test_deserialization() {
        $this->auth
            ->method("acl_get")
            ->with("f_read", 1)
            ->willReturn(true);

        $response = $this->routes->update_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => 1, "post_id" => 1),
            array("post_time" => "1514584956"),
            true
        );

        $this->assertEquals(array(
            "topic_id"        => "1",
            "post_id"         => "1",
            "poster_id"       => "1001",
            "username"        => "tom",
            "forum_id"        => "1",
            "post_text"       => "test",
            "bbcode_uid"      => "aaaaaaaa",
            "bbcode_bitfield" => "",
            "post_time"       => "2017-12-29T22:02:36+00:00",
            "post_number"     => "2",
            "post_bbcode"     => "test",
        ), $response);
    }
}
?>