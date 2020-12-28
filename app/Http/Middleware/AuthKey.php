<?php

namespace App\Http\Middleware;

use Closure;

class AuthKey
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
        $token = $request->header('Api-Key');
        if ($token != env('API_KEY')){
            return response()->json(['message' => 'Key not found'], 401);
        }
        return $next($request);
    }
}
