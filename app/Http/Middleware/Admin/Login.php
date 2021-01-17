<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Support\Facades\Session;

class Login
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty(Session::has('qqrot'))) {
            return redirect('/server/login');
        }
        return $next($request);
    }
}
