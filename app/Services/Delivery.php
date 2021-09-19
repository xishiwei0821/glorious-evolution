<?php

namespace App\Services;

use App\Exceptions\CustomException;

class Delivery
{
    private static $request_url;
    private static $customer;
    private static $key;

    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, ['getInfo'])) {
            throw new CustomException('Method ' . $name . 'does not allow access !');
        }

        self::$request_url = config('custom.delivery.url', '');
        self::$customer    = config('custom.delivery.customer', '');
        self::$key         = config('custom.delivery.key', '');

        return self::$name(...$arguments);
    }

    /**
     *  数据签名
     */
    private static function sign($params)
    {
        return strtoupper(md5(json_encode($params) . self::$key . self::$customer));
    }

    /**
     *  解析参数
     */
    private static function parseParams($parseData)
    {
        $post_data = "";
        foreach ($parseData as $key => $param) {
            $post_data .= $key . '=' . urlencode($param) . '&';
        }
        $post_data = substr($post_data, 0, -1);
        return $post_data;
    }

    /**
     *  查询物流信息
     *  @param string  $com 物流编码(详情查看对应快递信息)
     *  @param string  $num 物流编号
     *  @param integer $resultv2 是否显示其他信息
     *
     *  @return array
     */
    public static function getInfo($com, $num, $resultv2 = 0)
    {
        $params = [
            'com' => $com,
            'num' => $num,
            'resultv2' => $resultv2
        ];

        $parseData = [
            'customer' => self::$customer,
            'param'    => json_encode($params),
            'sign'     => self::sign($params)
        ];

        $result = request_curl(self::$request_url, self::parseParams($parseData));

        return $result;
    }
}
