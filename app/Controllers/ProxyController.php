<?php

namespace App\Controllers;

use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;
use GuzzleHttp\Client;

class ProxyController extends BaseController
{
    public function index(Request $request): Response
    {
        $r = $request->get('r', 'http://httpbin.org/anything?r=x');
        $res = (new Client())->get($r);

        return new Response(200, [
            'Content-Type' => implode('', $res->getHeader('Content-Type'))
        ], $res->getBody()->getContents());
    }
}