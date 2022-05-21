<?php

namespace App\Controllers;

use Intervention\Image\ImageManager;
use Workerman\Protocols\Http\{Request, Response};
use Symfony\Contracts\Cache\ItemInterface;

class ImageController extends BaseController
{
    public function index(Request $request): Response
    {
        $get = new \stdClass();
        $get->r = $request->get('r', 'https://picsum.photos/200/300?1=1');
        $get->ext = $request->get('ext', $request->get('e'));

        if (!in_array($get->ext, explode(',', 'jpg,png,webp,avif'))) $get->ext = 'webp';

        $cache_key = md5(__METHOD__ . implode(',', (array)$get));
        $cache = cache();
        if ($request->get('debug')) $cache->delete($cache_key);

        $res = $cache->get($cache_key, function (ItemInterface $item) use ($get) {
            $item->expiresAfter(60 * 60 * 24);

            $config = [
                //'driver' => extension_loaded('imagick') ? 'imagick' : 'gd',
            ];

            return (new ImageManager($config))
                ->make($get->r)
                ->stream($get->ext, 60)
                ->getContents();
        });

        return new Response(200, [
            'Content-Type' => 'image/' . $get->ext
        ], $res);
    }
}