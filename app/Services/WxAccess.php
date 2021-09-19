<?php

namespace App\Services;

use App\Exceptions\CustomException;

class WxAccess
{
    private static $appid;
    private static $secret;
    private static $templateList;

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, ['getOpenId', 'sendMessage', 'getAccessToken'])) {
            throw new CustomException('Method ' . $name . 'does not allow access !');
        }

        self::$appid        = config('wx.appid');
        self::$secret       = config('wx.secret');
        self::$templateList = config('wx.templateList');
        return self::$name(...$arguments);
    }

    /**
     *  获取用户openid
     *  @param string $js_code
     *  @return array
     */
    private static function getOpenId($js_code)
    {
        $request_url = "https://api.weixin.qq.com/sns/jscode2session";

        $result = request_curl(
            $request_url,
            'get',
            [
                'appid'      => self::$appid,
                'secret'     => self::$secret,
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
        $cacheInfo = cache('access_token');
        if (empty($cacheInfo) || $cacheInfo['created_at'] + $cacheInfo['expires_in'] < time()) {
            $request_url = "https://api.weixin.qq.com/cgi-bin/token";

            $result = request_curl(
                $request_url,
                'get',
                [
                    'grant_type' => 'client_credential',
                    'appid'      => self::$appid,
                    'secret'     => self::$secret
                ],
                [],
                true
            );

            $result['created_at'] = time();

            cache('access_token', $result);
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

        if ($dataObj->watermark->appid != self::$appid) {
            return '';
        }

        $data = $result;

        return json_decode($data, true);
    }

    /**
     *  发送订阅消息
     *  @param string $user_open_id 用户openid
     *  @param integer $message_type 发送类型
     *  @param object $data 参数
     *  @param string $page 跳转页面
     *  @return object
     */
    private static function sendMessage($user_open_id, $message_type, $data = [], $page = '')
    {
        // 通过接口获取access_token
        $access_token = self::getAccessToken();

        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $access_token;

        $result = request_curl(
            $url,
            'post',
            json_encode([
                'access_token' => $access_token,
                'touser'       => $user_open_id,
                'template_id'  => self::$templateList[$message_type]['id'],
                'page'         => $page,
                'data'         => $data,
                // 'miniprogram_state' => 'trial'
            ]),
            [],
            true
        );

        return $result;
    }
}
