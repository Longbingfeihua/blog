<?php

namespace App\Http\Middleware;

use Closure;

class testrouter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        return 123;
        return $next($request);
    }
}
