<?php

namespace App\Controllers;

use Workerman\Protocols\Http\{Request, Response};
use GuzzleHttp\Client;

class ProxyController extends BaseController
{
    public function index(Request $request)
    {
        $r = $request->get('r', 'http://httpbin.org/anything');
        return (new Client())->get($r)->getBody()->getContents();
    }
}