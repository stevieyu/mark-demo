<?php

namespace App\Controllers;

use Intervention\Image\ImageManager;
use Workerman\Protocols\Http\{Request, Response};

class ImageController extends BaseController
{
    public function index(Request $request): Response
    {
        $r = $request->get('r', 'https://picsum.photos/200/300');
        $ext = $request->get('ext', 'jpg');

        if(in_array($ext, explode('jpg,png,webp,avif', ','))) $ext = 'png';

        $config = [
//            'driver' => extension_loaded('imagick') ?'imagick':'gd',
            'cache' => [
                'path' => '/tmp/storage/cache'
            ]
        ];

        $res = (new ImageManager($config))
            ->cache(fn($image) => $image->make($r), 60, true)
            ->stream($ext, 60);

        return new Response(200, [
            'Content-Type' => 'image/'.$ext
        ], $res);
    }
}