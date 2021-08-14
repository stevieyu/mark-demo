<?php

namespace App\Controllers;

use Intervention\Image\ImageManager;
use Workerman\Protocols\Http\{Request, Response};

class ImageController extends BaseController
{
    public function index(Request $request)
    {
        $r = $request->get('r', 'https://picsum.photos/200/300');
        $ext = $request->get('webp', 'jpg');

        $res = (new ImageManager([]))
            ->cache(fn($image) => $image->make($r), 60, true)
            ->stream($ext, 60);

        return new Response(200, [
            'Content-Type' => 'image/'.$ext
        ], $res);
    }
}