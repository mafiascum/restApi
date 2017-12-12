<?php

namespace mafiascum\restApi\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once(dirname(__FILE__) . "/../model/resource/resourceFactory.php");
require_once(dirname(__FILE__) . "/../api/routes.php");

use mafiascum\restApi\model\resource\ResourceFactory;
use mafiascum\restApi\api\Routes;

class RestApi {
    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\request\request */
    protected $request;

    /* @var \phpbb\db\driver\driver */
	protected $db;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\user_loader */
    protected $user_loader;

    /* @var \phpbb\auth\auth */
    protected $auth;

    /* phpbb\language\language */
    protected $language;


    // resources
    protected $topic_resource;

    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db,  \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->request = $request;
        $this->db = $db;
        $this->user = $user;
        $this->user_loader = $user_loader;
        $this->language = $language;
        $this->auth = $auth;

        $this->params = $this->request_to_params();

        $this->routes = new ResourceFactory(Routes::$routes);
    }

    protected function request_to_params() {
        $params = array();
        $params['limit'] = $this->request->variable('limit', 50);
        $params['start'] = $this->request->variable('start', 0);

        foreach($this->request->variable_names(\phpbb\request\request_interface::GET) as $key) {
            if ($key != 'limit' && $key != 'start') {
                $params[$key] = $this->db->sql_escape($this->request->variable($key, ''));
            }
        }
        return $params;
    }

    public function topics_list() {
        return new JsonResponse($this->routes->list_resources(
            $this->db,
            $this->auth,
            array("topics"),
            array(),
            $this->params
        ));
    }

    public function topics_retrieve($id) {
        $response = $this->routes->retrieve_resource(
            $this->db,
            $this->auth,
            array("topics"),
            array("topic_id" => $id),
            $this->params,
            true
        );
        if (is_null($response)) {
            return new JsonResponse(array("reason" => "Resource with id '" . $id . "' does not exist."), Response::HTTP_NOT_FOUND);
        }
        return new JsonResponse($response);
    }

    public function topics_posts_list($id) {
        return new JsonResponse($this->routes->list_resources(
            $this->db,
            $this->auth,
            array("topics", "posts"),
            array("topic_id" => $id),
            $this->params,
            true
        ));
    }
}
?>