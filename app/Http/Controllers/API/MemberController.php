<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Member;
use App\Department;
use App\User;
use Carbon\Carbon;
use Image;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::with('user:id,avatar,active')->get([
          'user_id',
          'name',
          'surname',
          'birthday',
          'email',
        ])->where('user.active', '=', 1);

        return response()->json($members);
    }

    public function show($id)
    {
        $member = Member::with('user:id,avatar')->find($id, [
          'user_id',
          'name',
          'surname',
          'birthday',
          'email',
          'phone_1',
          'phone_2',
          'about',
          'department_id',
        ]);

        return response()->json($member);
    }
}
