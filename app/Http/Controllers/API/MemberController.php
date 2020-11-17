<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Member;
use App\Department;
use App\Position;
use App\User;
use Carbon\Carbon;
use Image;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $members = Member::select([
            'user_id',
            'name',
            'surname',
            'birthday',
            'email',
            'about',
            'department_id',
            'position_id',
        ])->with('user:id,avatar,active', 'department:id,name', 'position:id,name');

        if ($request->filled('department')) {
            $members->where('department_id', $request->get('department'));
        }

        if ($request->filled('name')) {
            $members->where('name', 'like', '%' . $request->get('name') . '%');
        }

        return response()->json($members->get()->where('user.active', 1));
    }

    public function show($id)
    {
        $member = Member::with('user:id,avatar', 'department:id,name', 'position:id,name')->find($id, [
          'user_id',
          'name',
          'surname',
          'birthday',
          'email',
          'phone_1',
          'phone_2',
          'about',
          'department_id',
          'position_id',
        ]);

        return response()->json($member);
    }
}
