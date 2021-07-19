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

$api->get('/qrcode', function (Request $requst) use ($cache){
    $get = new \stdClass();
    $get->data = $requst->get('data') ?? 'xxxxx';
    $get->size = (int)($requst->get('size') ?? 300);
    $get->ext = $requst->get('ext') ?? 'svg';

    $cache_key = md5('qrcode'.implode(',', (array)$get));

    // debug delete cache
    // $cache->delete($cache_key);
    
    $result = $cache->get($cache_key, function (ItemInterface $item) use ($get) {
        $item->expiresAfter(60 * 60);

        $result = Builder::create()
            ->writer($get->ext == 'png' ? new PngWriter() : new SvgWriter())
            ->writerOptions([])
            ->data($get->data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size($get->size)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->build();

        return [
            'mime_type' => $result->getMimeType(),
            'string' =>  $result->getString()
        ];
    });

    return new Response(200, [
        'Content-Type' => $result['mime_type']
    ], $result['string']);
});

$api->start();
