<?php

namespace App\Http\Middleware;

use Closure;

class Application
{
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}
