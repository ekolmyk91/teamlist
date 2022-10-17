<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    public function getCurrentUser()
    {
        $user = Auth::user();

        return response()->json($user->only(['id', 'roles']));
    }
}
