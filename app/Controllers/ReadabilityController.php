<?php

namespace App\Controllers;

use Workerman\Protocols\Http\Request;
use Readability\Readability;

class ReadabilityController extends BaseController
{
    public function index(Request $request): array
    {
        // read https://content-parser.com/

        $url = $request->get('r', 'https://www.cnblogs.com/iamzyf/p/3529740.html');
        $html = file_get_contents($url);

        $readability = new Readability($html, $url);
        // or without Tidy
        // $readability = new Readability($html, $url, 'libxml', false);
        $result = $readability->init();

        if(!$result) return [];

        return [
            'title' => $readability->getTitle()->textContent,
            'html' => $readability->getContent()->getInnerHtml(),
//            'text' => $readability->getContent()->textContent,
        ];
    }
}