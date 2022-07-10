<?php

namespace App\Controllers;

use Symfony\Contracts\Cache\ItemInterface;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use GuzzleHttp\Client;

class ProxyController extends BaseController
{
    public function index(Request $request): Response
    {
        $r = $request->get('r', 'http://httpbin.org/anything?r=x');

        $cache_key = md5(__METHOD__ . $r);
        $cache = cache();
        if ($request->get('debug')) $cache->delete($cache_key);

        $res = $cache->get($cache_key, function (ItemInterface $item) use ($r) {
            $item->expiresAfter(60 * 60 * 24);
            $res = (new Client())->get($r);
            return [
                'headers' => [
                    'Content-Type' => implode('', $res->getHeader('Content-Type')),
                ],
                'body' => $res->getBody()->getContents()
            ];
        });

        return new Response(200, [
            ...$res['headers'],
            'Cross-Origin-Resource-Policy' => 'cross-origin'
        ], $res['body']);
    }
}