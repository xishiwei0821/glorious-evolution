<?php

namespace App\Http\Controllers\Wxpublic;

use App\Http\Controllers\Controller;
use App\Services\WxAccess;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public $request;
    private $token = "9efc667952e2738cdcc780b1eafead01";

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function wx_access()
    {
        $signature = $this->request->get('signature');
        $timestamp = $this->request->get('timestamp');
        $nonce     = $this->request->get('nonce');
        $echostr   = $this->request->get('echostr');
        $token     = $this->token;

        if (empty($signature) || empty($timestamp) || empty($nonce) || empty($echostr) || empty($token)) {
            return false;
        }

        $array = [
            $token, $timestamp, $nonce
        ];

        sort($array, SORT_STRING);

        $hashcode = sha1(implode('', $array));

        if ($hashcode !== $signature) {
            return false;
        }

        return $echostr;
    }

    public function menus()
    {
        $menus = [
            'button' => [
                [
                    'name' => '扫码',
                    'sub_button' => [
                        [
                            'type' => 'scancode_waitmsg',
                            'name' => '扫码带提示',
                            'key'  => 'rselfmenu_0_0',
                            'sub_button' => []
                        ]
                    ]
                ]
            ]
        ];

        $menus = json_encode($menus);
        $access_token = WxAccess::getAccessToken();

        print_r($access_token);
        die;
        $request_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' + $access_token;

        $data = request_curl($request_url, 'post', $menus, [], true);

        print_r($data);
    }
}
