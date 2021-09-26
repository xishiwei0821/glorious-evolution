<?php

namespace App\Http\Controllers\Wxpublic;

use App\Http\Controllers\Controller;
use App\Services\WxAccess;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function wx_access()
    {
        $result = WxAccess::verifyCode();
        if (!$result) {
            return false;
        }

        return '';
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
        $request_url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;

        $data = request_curl($request_url, 'post', $menus, [], true);

        print_r($data);
    }
}
