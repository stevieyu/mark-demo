<?php

namespace App\Controllers;

use Workerman\Protocols\Http\{Request, Response};

use Endroid\QrCode\{Builder\Builder, Encoding\Encoding};
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\{PngWriter,SvgWriter};

use Symfony\Contracts\Cache\ItemInterface;

class QrcodeController extends BaseController
{
    public function index(Request $requst)
    {
        $get = new \stdClass();
        $get->data = $requst->get('data') ?? $requst->get('txt') ?? 'xxxxx';
        $get->size = (int)($requst->get('size') ?? 300);
        $get->ext = $requst->get('ext') ?? 'svg';

        $cache_key = md5('qrcode'.implode(',', (array)$get));

        $cache = cache();

        if($requst->get('debug')){
            $cache->delete($cache_key);
        }

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
    }
}