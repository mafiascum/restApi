<?php
namespace mafiascum\restApi\model\resource;

use Symfony\Component\HttpFoundation\Response;

class ResourceFactory {
    public function __construct($routes) {
        $this->routes = $routes;
    }

    private function find_resource($db, $auth, $language, $path, $ids) {
        foreach($path as $node) {
            if (is_null($resource)) {
                $resource_def = $this->routes[$node];
                $parent_record = array();
            } else {
                $resource_def = $resource->get_subresource_def($node);
                $pk_column = $resource->get_primary_key_column();
                $parent_record = array_merge(
                    $parent_record,
                    array($pk_column => $ids[$pk_column])
                );
            }
            $resource_clazz = $resource_def["impl"];
            $resource = new $resource_clazz(
                $db,
                $auth,
                $language,
                $resource_def,
                $parent_record
            );
        }
        return $resource;
    }

    public function list_resources($db, $auth, $language, $path, $ids, $params, $toClient = false) {
        $resource = $this->find_resource($db, $auth, $language, $path, $ids);
        $data = $resource->list($params);
        if ($toClient) {
            $data["data"] = array_map(
                function ($item) use ($resource) {
                    return $resource->to_json($item);
                },
                $data["data"]
            );
        }
        return $data;
    }

    public function retrieve_resource($db, $auth, $language, $path, $ids, $params, $toClient = false) {
        $resource = $this->find_resource($db, $auth, $language, $path, $ids);
        $pk_column = $resource->get_primary_key_column();
        $data = $resource->retrieve($ids[$pk_column], $params);
        if ($toClient) {
            return $resource->to_json($data);
        } else {
            return $data;
        }
    }

    public function delete_resource($db, $auth, $language, $path, $ids) {
        $resource = $this->find_resource($db, $auth, $language, $path, $ids);
        $pk_column = $resource->get_primary_key_column();
        $resource->delete($ids[$pk_column], $params);
    }

    public function create_resource($db, $auth, $language, $path, $ids, $data, $fromClient = false) {
        $resource = $this->find_resource($db, $auth, $language, $path, $ids);
        if ($fromClient) {
            $errors = $resource->validate(null, $data);
            if ($errors) {
                return array("status" => 400, "errors" => $errors);
            } else {
                return $resource->to_json($resource->create($resource->from_json($data)));
            }
        } else {
            return $resource->create($data);
        }
    }

    public function update_resource($db, $auth, $language, $path, $ids, $data, $fromClient = false) {
        $resource = $this->find_resource($db, $auth, $language, $path, $ids);
        $pk_column = $resource->get_primary_key_column();
        $id = $ids[$pk_column];
        if ($fromClient) {
            $errors = $resource->validate($id, $data);
            if ($errors) {
                return array("status" => Response::HTTP_BAD_REQUEST, "errors" => $errors);
            } else {
                return $resource->to_json($resource->update($id, $resource->from_json($data)));
            }
        } else {
            return $resource->update($id, $data);
        }
    }
}
?>