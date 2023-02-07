<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\OffTime;
use App\User;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    public function getCurrentUser()
    {
        $user = Auth::user();

        try {
            $user['statistic'] = OffTime::getOffTimeDaysCount($user->id);
            return response()->json($user->only(['id', 'roles', 'statistic']));
        } catch (\Throwable $t) {
            return response()->json($user->only(['id', 'roles']));
        }

    }
}
