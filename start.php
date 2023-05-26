<?php
require 'vendor/autoload.php';

use App\App;

ini_set('memory_limit',-1);

App::$pidFile = '/tmp/mark-run.pid';
App::$logFile = '/tmp/mark-run.log';

$api = new App('http://0.0.0.0:9000');

//$cores = (int)(PHP_OS_FAMILY == 'Windows' ? shell_exec('echo %NUMBER_OF_PROCESSORS%') : shell_exec('nproc'));
$api->count = 1; // process count

$files = new RecursiveDirectoryIterator('app/Controllers');

$responseHandle = function ($res) {
    $headers = [];
    if (is_object($res)) {
        if (method_exists($res, 'toString')) $res = $res->toString();
        if (method_exists($res, 'toArray')) $res = $res->toArray();
    }
    if (
        is_array($res)
        || $res instanceof \stdClass
    ) {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        $res = json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    if($res instanceof \Workerman\Protocols\Http\Response) return $res;
    return new \Workerman\Protocols\Http\Response(200, $headers, $res);
};
$indexHandle = function ($route) use ($api, $responseHandle) {
    $api->get('', fn($requst) => $responseHandle($route->index($requst)));
    $api->get('/', fn($requst) => $responseHandle($route->index($requst)));
};

foreach ($files as $file) {
    if (
        $file->isDir()
        || !$file->getExtension()
        || stristr($file->getFilename(), 'base')
    ) continue;

    $class_name = str_replace('.' . $file->getExtension(), '', $file->getFilename());
    $class_path = '\App\Controllers\\' . $class_name;
    $route = new $class_path();
    $methods = get_class_methods($route);

    $group_path = lcfirst(str_replace('Controller', '', $class_name));

    $groupHandle = function (App $api) use ($methods, $route, $indexHandle, $responseHandle) {
        foreach ($methods as $method) {
            if ($method == 'index') {
                $indexHandle($route);
                continue;
            }
            $hasMethod = false;
            foreach (['get', 'post', 'delete', 'put'] as $v) {
                if (strpos($method, $v) !== 0) continue;
                $pathName = str_replace($v, '', $method);
                if ($pathName == 'index') {
                    $indexHandle($route);
                } else {
                    $api->$v('/' . lcfirst($pathName), fn($requst) => $responseHandle($route->$method($requst)));
                }
                $hasMethod = true;
                break;
            }
            if (!$hasMethod) {
                $api->get('/' . lcfirst($method), fn($requst) => $responseHandle($route->$method($requst)));
            }
        }
    };

    if ($group_path === 'default') {
        $groupHandle($api);
    } else {
        $api->group('/' . $group_path, fn(App $api) => $groupHandle($api));
    }
}

$api->start();
