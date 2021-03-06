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
    public function index(Request $request): Response
    {
        $get = new \stdClass();
        $get->data = $request->get('data') ?? $request->get('txt') ?? $request->get('text') ?? 'xxxxx';
        $get->size = (int)($request->get('size') ?? 300);
        $get->ext = $request->get('ext') ?? 'svg';

        $cache_key = md5(__METHOD__.implode(',', (array)$get));
        $cache = cache();
        if($request->get('debug'))$cache->delete($cache_key);


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