<?php

namespace App\Http\Middleware;

use Closure;

class ApplicationVerifyToken
{
    public function handle($request, Closure $next)
    {
        // 验证token是否存在
        if (!$request->header('glory-token')) {
            $result = [
                'code' => 100002,
                'msg'  => 'token is unavailable',
                'data' => []
            ];
            return response()->json($result);
        }

        // 验证token是否可用，并获取用户信息

        return $next($request);
    }
}
