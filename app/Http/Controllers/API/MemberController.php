<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Member;
use App\Department;
use App\Position;
use App\Certificate;
use App\User;
use Carbon\Carbon;
use Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('month')) {
            $members = new Member();

            return response()->json([
                                    'birthPeople' => $members->getMembersListAccordingDate('birthday', $request->get('month')),
                                    'expPeople' => $members->getMembersListAccordingDate('start_work_day', $request->get('month') ),
                                    ]);
        } else {
            $params = [
                'user_id',
                'name',
                'surname',
                'city',
                'email',
                'department_id',
                'position_id',
                'trainee',
                'about',
            ];
            if (Auth::user()->hasRole(['manager', 'admin'])) {
               $params[] = 'phone_1';
            }

            $members = Member::select($params)->selectRaw("DATE_FORMAT(birthday, '%d/%m') as birthday, DATE_FORMAT(start_work_day, '%d/%m/%Y') as start_work_day")
                ->with('user:id,avatar,active', 'department:id,name', 'position:id,name', 'certificates:id,name,logo');

            if ($request->filled('department')) {
                $members->where('department_id', $request->get('department'));
            }

            if ($request->filled('name')) {
                $members->where('name', 'like', '%' . $request->get('name') . '%');

            }

            return response()->json($members->get()->where('user.active', 1));
        }
    }

    public function show($id)
    {
        $member = Member::with('user:id,avatar', 'department:id,name', 'position:id,name', 'certificates:id,name,logo')
                        ->find($id, [
                                  'user_id',
                                  'name',
                                  'surname',
                                  'birthday',
                                  'start_work_day',
                                  'email',
                                  'phone_1',
                                  'phone_2',
                                  'city',
                                  'about',
                                  'department_id',
                                  'position_id',
	                              'trainee'
                                ]);

        return response()->json($member);
    }
}
