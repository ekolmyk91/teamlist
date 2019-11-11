<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
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
//        $userRoles = Auth::user()->roles->pluck('name');
        $userRoles = Auth::user()->roles->pluck('name');

        if(!$userRoles->contains('admin')){
            return redirect('/404');
        }

        return $next($request);
    }
}
