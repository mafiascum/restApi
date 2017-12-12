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
            array("topics"),
            array("topic_id" => 1),
            array("limit" => 2, "start" => 8)
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
            array("topics", "posts"),
            array("topic_id" => 1),
            array("post_number" => "1")
        );

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals(2, $response['total']);
        $this->assertEquals(null, $response['next']);
        $this->assertEquals(null, $response['prev']);
    }
}
?>