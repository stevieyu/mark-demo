<?php

namespace App\Controllers;

class DefaultController extends BaseController
{
    public function index(): string
    {
        return 'Hello world';
    }

    public function exts(): array
    {
        $ret = [];

        if(extension_loaded('gd')) $ret['gd'] = gd_info();
        if(extension_loaded('imagick')) $ret['imagick'] = \Imagick::getVersion();

        return $ret;
    }

    public function test(): string
    {
        return '';
    }
}