<?php

namespace App\Services;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Cache;

class WxAccess
{
    private static $public_appid;
    private static $public_secret;
    private static $mini_appid;
    private static $mini_secret;
    private static $open_appid;
    private static $open_secret;

    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, ['getOpenId', 'sendMessage', 'getAccessToken'])) {
            throw new CustomException('Method ' . $name . 'does not allow access !');
        }

        self::$public_appid        = config('wx.public_appid');
        self::$public_secret       = config('wx.public_secret');

        self::$mini_appid        = config('wx.mini_appid');
        self::$mini_secret       = config('wx.mini_secret');

        self::$open_appid        = config('wx.open_appid');
        self::$open_secret       = config('wx.open_secret');
        return self::$name(...$arguments);
    }

    /**
     *  获取用户openid
     *  @param string $js_code
     *  @param string $type  ['PUBLIC' => '公众号', 'MINI' => '小程序', 'OPEN' => '开放平台']
     *  @return array
     */
    private static function getOpenId($js_code, $type = 'PUBLIC')
    {
        switch ($type) {
            case 'PUBLIC':
                $appid = self::$public_appid;
                $secret = self::$public_secret;
            break;
            case 'PUBLIC':
                $appid = self::$mini_appid;
                $secret = self::$mini_secret;
            break;
            case 'PUBLIC':
                $appid = self::$open_appid;
                $secret = self::$open_secret;
            break;
            default:
                $appid = self::$public_appid;
                $secret = self::$public_secret;
            break;
        }

        $request_url = "https://api.weixin.qq.com/sns/jscode2session";

        $result = request_curl(
            $request_url,
            'get',
            [
                'appid'      => $appid,
                'secret'     => $secret,
                'js_code'    => $js_code,
                'grant_type' => 'authorization_code'
            ],
            [],
            true
        );

        return $result;
    }

    /**
     *  获取access_token
     *  @return string
     */
    private static function getAccessToken()
    {
        $cacheInfo = Cache::get('access_token');
        if (empty($cacheInfo) || $cacheInfo['created_at'] + $cacheInfo['expires_in'] < time()) {
            $request_url = "https://api.weixin.qq.com/cgi-bin/token";

            $result = request_curl(
                $request_url,
                'get',
                [
                    'grant_type' => 'client_credential',
                    'appid'      => self::$public_appid,
                    'secret'     => self::$public_secret
                ],
                [],
                true
            );

            if (array_key_exists('errorcode', $result) && $result['errcode'] !== 0) {
                throw new CustomException($result['errmsg']);
            }

            $result['created_at'] = time();

            Cache::set('access_token', $result);
        } else {
            $result = $cacheInfo;
        }

        return $result['access_token'];
    }

    /**
     *  解密微信参数
     *  @param string $encryptedData 解密参数
     *  @param string $iv 偏移量
     *  @param string $sessionKey 用户sessionkey
     *  @return string 解密结果
     */
    private static function decryptParams($encryptedData, $iv, $sessionKey)
    {
        if (strlen($sessionKey) != 24) {
            return '';
        }

        $aesKey = base64_decode($sessionKey);

        if (strlen($iv) != 24) {
            return '';
        }

        $aesIV     = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result    = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj   = json_decode($result);

        if ($dataObj == null) {
            return '';
        }

        if ($dataObj->watermark->appid != self::$public_appid) {
            return '';
        }

        $data = $result;

        return json_decode($data, true);
    }

    // /**
    //  *  发送订阅消息
    //  *  @param string $user_open_id 用户openid
    //  *  @param integer $message_type 发送类型
    //  *  @param object $data 参数
    //  *  @param string $page 跳转页面
    //  *  @return object
    //  */
    // private static function sendMessage($user_open_id, $message_type, $data = [], $page = '')
    // {
    //     // 通过接口获取access_token
    //     $access_token = self::getAccessToken();

    //     $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $access_token;

    //     $result = request_curl(
    //         $url,
    //         'post',
    //         json_encode([
    //             'access_token' => $access_token,
    //             'touser'       => $user_open_id,
    //             'template_id'  => self::$templateList[$message_type]['id'],
    //             'page'         => $page,
    //             'data'         => $data,
    //             // 'miniprogram_state' => 'trial'
    //         ]),
    //         [],
    //         true
    //     );

    //     return $result;
    // }
}
