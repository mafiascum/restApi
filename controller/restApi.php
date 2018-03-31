<?php

namespace mafiascum\restApi\controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once(dirname(__FILE__) . "/../model/resource/resourceFactory.php");
require_once(dirname(__FILE__) . "/../api/routes.php");

use mafiascum\restApi\model\resource\ResourceFactory;
use mafiascum\restApi\api\Routes;
use Exception;

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

    /* phpbb\language\language */
    protected $language;

    /* @var \phpbb\auth\auth */
    protected $auth;


    // resources
    protected $topic_resource;

    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\db\driver\driver_interface $db,  \phpbb\user $user, \phpbb\user_loader $user_loader, \phpbb\language\language $language, \phpbb\auth\auth $auth)
    {
        global $table_prefix;

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

    private function getUser() {
        $apiAuthHeader = $this->request->header('X-Mafiascum-Api-Secret');
        
        if ($apiAuthHeader == getenv('API_CLIENT_SHARED_KEY')) {
            return $this->request->header('X-Mafiascum-Api-User') ?: ANONYMOUS;
        } else {
            return null;
        }
    }

    private function requireLogin() {
        $userId = $this->getUser();
        if ($userId == null) {
            throw new Exception("ERROR_UNAUTHORIZED");
        } else {
            $this->user->session_create($userId);
        }
    }

    private function respondUnauthorized($e) {
        return self::resource_to_json(array(
            "errors" => array(
                array(
                    "type" => "unauthorized",
                    "message" => $this->language->lang($e->getMessage()),
                ),
            ),
            "status" => Response::HTTP_UNAUTHORIZED));
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

    public static function resource_to_json($response) {
        if (isset($response["status"])) {
            $status = $response["status"];
            unset($response["status"]);
            return new JsonResponse($response, $status);
        } else {
            return new JsonResponse($response);
        }
    }

    public function topics_list() {
        try {
            $this->requireLogin();
        } catch (\Exception $e) {
            return $this->respondUnauthorized($e);
        }
        
        return self::resource_to_json($this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array(),
            $this->params,
            true
        ));
    }

    public function topics_retrieve($id) {
        try {
            $this->requireLogin();
        } catch (\Exception $e) {
            return $this->respondUnauthorized($e);
        }
        
        $response = $this->routes->retrieve_resource(
            $this->db,
            $this->auth,
            $this->language,
            array("topics"),
            array("topic_id" => $id),
            $this->params,
            true
        );
        if (is_null($response)) {
            return self::resource_to_json(array(
                "errors" => array(
                    array(
                        "type" => "not_found",
                        "message" => $this->language->lang("ERROR_NOT_FOUND"),
                    ),
                ),
                "status" => Response::HTTP_NOT_FOUND));
        } else {
            return self::resource_to_json($response);
        }
    }

    public function topics_posts_list($id) {
        try {
            $this->requireLogin();
        } catch (\Exception $e) {
            return $this->respondUnauthorized($e);
        }
        
        return self::resource_to_json($this->routes->list_resources(
            $this->db,
            $this->auth,
            $this->language,
            array("topics", "posts"),
            array("topic_id" => $id),
            $this->params,
            true
        ));
    }
}
?>