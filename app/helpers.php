<?php

// 记录一些常用到的函数
/**
 *  -----------------------------------------------------------------------------------------
 *  @array_to_tree      普通数组根据id，pid转化为树结构
 *  @request_curl       发送curl请求
 *  @getMicrotime       获取13位时间戳
 *  @get_sex_by_idcard  获取性别（通过身份证）
 *  @get_countdown_time 获取倒计时时间
 *  -----------------------------------------------------------------------------------------
 */

/**
 *  普通数组根据id，pid转化为树结构
 *  @param array  $array    转化的数组
 *  @param string $id       主键名称
 *  @param string $pid      上级主键名称
 *  @param string $children 下级目录索引名称
 *
 *  @return array|boolean   转化成功返回结果数组，转化失败/数组格式错误返回false
 */
if (!function_exists('arrar_to_tree')) {
    function array_to_tree($array, $id = 'id', $pid = 'pid', $children = 'children')
    {
        if ((!is_array($array)) || count($array) <= 0) {
            return false;
        }

        $array = array_values($array);

        if (!array_key_exists($id, $array[0])) {
            return false;
        }

        if (!array_key_exists($pid, $array[0])) {
            return false;
        }

        $items = [];

        foreach ($array as $value) {
            $value[$children] = [];
            $items[$value[$id]] = $value;
        }

        $tree = [];

        foreach ($items as $key => $item) {
            if (isset($items[$item[$pid]])) {
                $items[$item[$pid]][$children][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }

        return $tree;
    }
}

/**
 *  发送curl请求获取数据
 *  @param string $request_url 请求地址
 *  @param string $type        请求类型(GET, POST)
 *  @param array  $data        传递参数
 *  @param array  $header      自定义header
 *  @param boolean $https      是否启用https
 *
 *  @return string|boolean
 */
if (!function_exists('request_curl')) {
    function request_curl($request_url, $type = 'GET', $data = [], $header = [], $https = false)
    {
        if (!in_array(strtoupper($type), ['GET', 'POST'])) {
            return false;
        }

        $defaultHeader = [ 'Accept: application/json;' ];
        if (!empty($header)) {
            $headers = array_merge($header, $defaultHeader);
        } else {
            $headers = $defaultHeader;
        }

        // 开始curl请求
        $curl = curl_init();

        // 如果是get请求，拼接数据到url
        if (strtoupper($type) === 'GET' && !empty($data)) {
            $params = '';
            foreach ($data as $key => $value) {
                $params .= $key . '=' . $value . '&';
            }

            $params = rtrim($params, '&');

            $request_url = $request_url . '?' . $params;
        }

        curl_setopt($curl, CURLOPT_URL, $request_url);

        // 如果是post请求
        if (strtoupper($type) === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        // 设置请求头
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        // 设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 0);
        // 设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        // 如果是https请求
        if ($https) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        // 执行结果
        $response = curl_exec($curl);
        $errors   = curl_error($curl);
        curl_close($curl);

        // 显示错误信息
        if ($errors) {
            return false;
        }

        // 去除转义符号
        $response = str_replace("\"", '"', $response);
        $data = json_decode($response, true);
        return $data;
    }
}

/**
 *  获取13位时间戳
 *
 *  @return float
 */
if (!function_exists('getMicrotime')) {
    function getMicrotime()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}

/**
 *  根据身份证号码获取性别
 *  @param  string  $idcard    身份证号码
 *
 *  @return integer $sex       性别 1男 2女 0未知
 */
if (!function_exists('get_sex_by_idcard')) {
    function get_sex_by_idcard($idcard)
    {
        if (empty($idcard)) {
            return 0;
        }
        $sexint = (int)substr($idcard, 16, 1);
        return $sexint % 2 === 0 ? 0 : 1;
    }
}

/**
 *  获取倒计时时间，例如5s前，1小时前
 *  @param integer $timestamp 时间戳
 *  @param string  $type  类型（SECOND:xx秒前;MINUTE: 分钟前;HOUR: 小时前;DAY: 天前; AUTO: 自动判定;） default AUTO
 *
 *  @return string
 */
if (!function_exists('get_countdown_time')) {
    function get_countdown_time($timestamp, $type = 'AUTO')
    {
        $now = time();
        $diff = $now - (float)$timestamp;

        switch ($type) {
            case 'AUTO':
                if ($diff <= 60) {
                    $result = $diff . '秒前';
                } elseif ($diff <= 60 * 60) {
                    $result = floor($diff / 60) . '分钟前';
                } elseif ($diff <= 60 * 60 * 24) {
                    $result = floor($diff / 60 / 60) . '小时前';
                } elseif ($diff <= 60 * 60 * 24 * 10) {
                    $result = floor($diff / 60 / 60 / 24) . '天前';
                } else {
                    $result = strtotime((int)$timestamp);
                }
                break;
            case 'SECOND':
                $result = $diff . '秒前';
                break;
            case 'MINUTE':
                $result = (float)($diff / 60) . '分钟前';
                break;
            case 'HOUR':
                $result = (float)($diff / 60 / 60) . '小时前';
                break;
            case 'DAY':
                $result = (float)($diff / 60 / 60 / 24) . '天前';
                break;
            default:
                get_countdown_time($timestamp, 'AUTO');
                break;
        }

        return $result;
    }

    if (!function_exists('success_json')) {
        function success_json($data = [], $msg = 'success', $code = 100000)
        {
            $result = [
                'code' => $code,
                'msg'  => $msg,
                'data' => $data
            ];

            return response()->json($result);
        }
    }

    if (!function_exists('error_json')) {
        function error_json($code, $msg = 'error', $data = [])
        {
            $result = [
                'code' => $code,
                'msg'  => $msg,
                'data' => $data
            ];

            return response()->json($result);
        }
    }

    if (!function_exists('diff_version')) {
        function diff_version($version, $lasted_version, $prefix = '', $time = 0)
        {
            $version            = substr($version, strlen($prefix));
            $lasted_version     = substr($lasted_version, strlen($prefix));

            $version_arr        = explode('.', $version);
            $lasted_version_arr = explode('.', $lasted_version);

            if (!array_key_exists($time, $lasted_version_arr)) {
                return 1;
            }

            if (!array_key_exists($time, $version_arr)) {
                $version_arr[$time] = 0;
            }

            $version_time        = $version_arr[$time];
            $lasted_version_time = $lasted_version_arr[$time];

            if ((int)$version_time === (int)$lasted_version_time) {
                $time ++;
                return diff_version($version, $lasted_version, '', $time);
            }

            if ((int)$version_time > (int)$lasted_version_time) {
                return 1;
            }

            if ((int)$version_time < (int)$lasted_version_time) {
                return -1;
            }
        }
    }
}
