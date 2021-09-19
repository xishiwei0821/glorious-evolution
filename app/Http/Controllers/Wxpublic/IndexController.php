<?php

namespace App\Http\Controllers\Wxpublic;

use App\Http\Controllers\Controller;
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
            return "Error: 获取参数错误";
        }

        $array = [
            $token, $timestamp, $nonce
        ];

        sort($array, 'SORT_STRING');

        $hashcode = sha1(implode('', $array));

        if ($hashcode !== $signature) {
            return "Error: 签名错误";
        }

        return $echostr;
    }
}