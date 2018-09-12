<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * 跨域中间件 目前使用 \Barryvdh\Cors 中间件
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', '*');
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Access-Token');
        $response->header('Access-Control-Expose-Headers', 'Origin, Content-Type, Accept, Access-Token');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
        $response->header('Access-Control-Allow-Credentials', 'false');

        return $response;
    }
}
