<?php

namespace App\Controllers;

use Workerman\Protocols\Http\{Request, Response};
use GuzzleHttp\Client;

class ProxyController extends BaseController
{
    public function index(Request $request): string
    {
        $r = $request->get('r', 'http://httpbin.org/anything?r=x');
        return (new Client())->get($r)->getBody()->getContents();
    }
}