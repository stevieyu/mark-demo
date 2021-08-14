<?php

namespace App\Controllers;

use EasyWeChat\Factory;
use Workerman\Protocols\Http\Request;
use Workerman\Connection\TcpConnection;

class WechatController extends BaseController
{
    public function signature(Request $request)
    {
        $app = Factory::officialAccount([
            'app_id' => 'wxbeace8cd80da4952',
            'secret' => 'e9c779ef6b0ffecf4ac49c51959190d9',
        ]);

        $url = $request->get('url', $request->header('referer'));
        $url = str_replace(strstr($url, '#'), '', $url);

        return $app->jssdk
            ->setUrl($url)
            ->buildConfig([
                'updateAppMessageShareData',
                'updateTimelineShareData',
                'onMenuShareWeibo',
                'onMenuShareQZone',
               /* 'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'onVoicePlayEnd',
                'uploadVoice',
                'downloadVoice',*/
                /*'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',*/
//                'translateVoice',
                'getNetworkType',
//                'openLocation',
//                'getLocation',
//                'hideOptionMenu',
//                'showOptionMenu',
//                'hideMenuItems',
//                'showMenuItems',
//                'hideAllNonBaseMenuItem',
//                'showAllNonBaseMenuItem',
                'closeWindow',
                'scanQRCode',
                'chooseWXPay',
//                'openProductSpecificView',
                /*'addCard',
                'chooseCard',
                'openCard',*/
            ], $request->get('debug', false), false, true, []);
    }
}