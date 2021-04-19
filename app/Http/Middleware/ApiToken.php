<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class ApiToken
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
        if (!$request->cookie('tml-cookie')) {
            $api_token = User::find(Auth::user()->id)->api_token;
            $response = $next($request);

            return $response->cookie('tml-cookie', $api_token, 0, '/', null, false, false);
        }

        return $next($request);
    }
}
