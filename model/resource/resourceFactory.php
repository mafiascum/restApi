<?php
namespace mafiascum\restApi\model\resource;

require_once("manifest.php");

use mafiascum\restApi\model\resource\ResourceManifest;

class ResourceFactory {
    private static function find_resource($db, $auth, $path, $ids) {
        foreach($path as $node) {
            if (is_null($resource)) {
                $resource_def = ResourceManifest::$resources[$node];
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
                $resource_def,
                $parent_record
            );
        }
        return $resource;
    }

    public static function list_resources($db, $auth, $path, $ids, $request, $shouldSerialize = false) {
        $resource = ResourceFactory::find_resource($db, $auth, $path, $ids);
        $data = ResourceFactory::find_resource($db, $auth, $path, $ids)->list($request);
        if ($shouldSerialize) {
            $data["data"] = array_map(
                function ($item) use ($resource) {
                    return $resource->to_json($item);
                },
                $data["data"]
            );
        }
        return $data;
    }

    public static function retrieve_resource($db, $auth, $path, $ids, $request, $shouldSerialize = false) {
        $resource = ResourceFactory::find_resource($db, $auth, $path, $ids);
        $pk_column = $resource->get_primary_key_column();
        $data = $resource->retrieve($ids[$pk_column], $request);
        if ($shouldSerialize) {
            return $resource->to_json($data);
        } else {
            return $data;
        }
    }
}
?>