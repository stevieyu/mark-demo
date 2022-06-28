<?php

namespace App\Controllers;

use Workerman\Protocols\Http\Request;
use Readability\Readability;
use GuzzleHttp\Client;

class ReadabilityController extends BaseController
{
    public function index(Request $request): array
    {
        $url = $request->get('r', 'https://www.cnblogs.com/iamzyf/p/3529740.html');
        $html = (new Client())->get($url)->getBody()->getContents();

        $readability = new Readability($html, $url);
        // or without Tidy
        // $readability = new Readability($html, $url, 'libxml', false);
        $result = $readability->init();

        if(!$result) return [];

        return [
            'title' => $readability->getTitle()->textContent,
            'content' => $readability->getContent()->textContent,
        ];
    }
}