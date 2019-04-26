<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class BankPortal
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
        if (!Session::has('bank_login')) {
            return redirect()->to('/bank/login');
        }
        return $next($request);
    }
}