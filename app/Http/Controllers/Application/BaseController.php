<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public static $request;

    public function __construct(Request $request)
    {
        self::$request = $request;
    }

    public static function __callStatic($name, $arguments)
    {
        if (!in_array($name, [ 'checked_update' ])) {
            return error_json(100001, 'Can\'t access the method', []);
        }

        return self::$name(...$arguments);
    }

    private static function checked_update()
    {
        // 获取版本号和code
        $version        = self::$request->header('version');
        $lasted_version = config('application.version');

        if (empty($version) || empty($lasted_version)) {
            return error_json(100002, 'Can\'t get the version of the app', []);
        }

        $result = [
            'is_need_update'  => 0,
            'lasted_version'  => $lasted_version,
            'current_version' => $version
        ];

        // 验证版本号
        if ((int)diff_version($version, $lasted_version) < 0) {
            // 需要更新
            $result = [
                'is_need_update'  => 1,
                'lasted_version'  => $lasted_version,
                'current_version' => $version
            ];
        }

        return $result;
    }
}
