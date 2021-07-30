<?php
require 'vendor/autoload.php';

use Mark\App;

use Workerman\Protocols\Http\{Request, Response};

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\PngWriter;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;

$cache = new FilesystemAdapter('', 0, '/tmp/mark_cache');

App::$pidFile = '/tmp/mark-run.pid';
App::$logFile = '/tmp/mark-run.log';

$api = new App('http://0.0.0.0:9000');

$api->count = 2; // process count

$api->get('/', function () {
    return 'Hello world';
});

$files = new RecursiveDirectoryIterator('app/Controllers');
foreach ($files as $file){
    if(
        $file->isDir()
        || !$file->getExtension()
        || stristr( $file->getFilename(), 'base')
    ) continue;

    $class_name = str_replace( '.'.$file->getExtension(), '', $file->getFilename());
    $class_path = '\App\Controllers\\'.$class_name;
    $route = new $class_path();
    $methods = get_class_methods($route);

    $group_path = lcfirst(str_replace('Controller', '', $class_name));

    $api->group('/'.$group_path, function(App $api) use ($methods, $route){
        foreach ($methods as $method){
            if($method == 'index') {
                $api->get( '', fn($requst) => $route->$method($requst));
                $api->get( '/', fn($requst) => $route->$method($requst));
                continue;
            }
            foreach (['get', 'post', 'delete', 'put'] as $v){
                if(strpos($method, $v) !== 0) continue;
                $api->$v('/'.lcfirst(str_replace($v, '', $method)), fn($requst) => $route->$method($requst));
                break;
            }
        }
    });
}

$api->start();
