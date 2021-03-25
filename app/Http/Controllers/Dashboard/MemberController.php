<?php

namespace App\Http\Controllers\Dashboard;

use App\Certificate;
use App\Http\Controllers\Controller;
use App\Member;
use App\Department;
use App\Position;
use App\Role;
use App\User;
use Carbon\Carbon;
use Image;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except('logout');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $members = Member::paginate(10);

        if ($request->filled('department')) {
            $members->where('department_id', $request->get('department'));
        }

        return view('dashboard.member.index', ['members' => $members]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::all();
        $positions = Position::all();
        $certificates = Certificate::all()->pluck('name', 'id');

        return view('dashboard.member.create', compact('departments', 'positions', 'certificates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $date = Carbon::now()->subYears(16)->addDay(1)->timestamp;
        $request->validate([
          'name'           =>'required|string|min:2|max:20',
          'surname'        =>'required|string|min:2|max:40',
          'email'          =>'required|email|unique:users',
          'password'       =>'required|string|min:8',
          'birthday'       =>'required|date|before:today',
          'start_work_day' =>'nullable|date|before:today',
          'phone_1'        =>'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members',
          'phone_2'        =>'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members',
          'department'     =>'required',
          'position'       =>'required',
          'certificates'   => 'nullable|array',
          'about'          =>'nullable|string|max:1000',
          'avatar'         =>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $active = $request->get('active');
        $userFields = [
          'name'     => $request->get('name'),
          'email'    => $request->get('email'),
          'password' => password_hash($request->get('password'), PASSWORD_BCRYPT),
          'active' => isset($active) ? 1 : 0,
        ];

        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $filename = time() . '-' . $avatar->getClientOriginalName();

            request()->file('avatar')->storeAs('avatar', $filename);
            $userFields['avatar'] = $filename;
        }

        //Create User entity.
        $user = new User($userFields);
        $user->save();

        $start_work_day = $request->get('start_work_day');

        //Create Member entity and attach User.
        $member = new Member([
          'name' => $request->get('name'),
          'surname' => $request->get('surname'),
          'email' => $request->get('email'),
          'phone_1' => $request->get('phone_1'),
          'phone_2' => $request->get('phone_2'),
          'birthday' => Carbon::parse($request->get('birthday')),
          'start_work_day' => isset($start_work_day) ? Carbon::parse($start_work_day) : null,
          'about' => $request->get('about'),
          'department_id' => $request->get('department'),
          'position_id' => $request->get('position'),
          'user'  => $user
        ]);
        $member->save();
        if(!empty($request->certificate[0])) {
            $member->certificates()->sync($request->input('certificate', []));
        }

        return redirect()->action('Dashboard\MemberController@index')->with('success', 'Member saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function edit($user_id)
    {
        $member      = Member::find($user_id);
        $departments = Department::all();
        $positions = Position::all();
        $certificates = Certificate::all()->pluck('name', 'id');

        return view('dashboard.member.edit', [
          'member'      => $member,
          'departments' => $departments,
          'positions' => $positions,
          'certificates' => $certificates
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'           =>'required|string|min:2|max:20',
            'surname'        =>'required|string|min:2|max:40',
            'email'          =>'required|email|unique:users,email,' . $id,
            'birthday'       =>'required|date|before:today',
            'start_work_day' =>'nullable|date|before:today',
            'phone_1'        =>'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members,phone_1,' . $id .',user_id',
            'phone_2'        =>'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members,phone_2,' . $id .',user_id',
            'department'     =>'required',
            'position'       =>'required',
            'certificates'   => 'nullable|array',
            'about'          =>'nullable|string|max:1000',
            'avatar'         =>'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $member = Member::find($id);

        $active = $request->get('active');
        $userFields = [
            'name' => $request->get('name'),
            'active' => isset($active) ? 1 : 0,
        ];
        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $filename = time() . '-' . $avatar->getClientOriginalName();

            request()->file('avatar')->storeAs('avatar', $filename);
            $userFields['avatar'] = $filename;
        }

        $start_work_day = $request->get('start_work_day');

        $member->user()->update($userFields);
        $member->update([
            'name' => $request->get('name'),
            'surname' => $request->get('surname'),
            'email' => $request->get('email'),
            'phone_1' => $request->get('phone_1'),
            'phone_2' => $request->get('phone_2'),
            'birthday' => Carbon::parse($request->get('birthday')),
            'start_work_day' => isset($start_work_day) ? Carbon::parse($start_work_day) : null,
            'about' => $request->get('about'),
            'department_id' => $request->get('department'),
            'position_id' => $request->get('position'),
        ]);

        if(!empty($request->certificate[0])) {
            $member->certificates()->sync($request->input('certificate', []));
        } else {
            $member->certificates()->sync([]);
        }

        return redirect()->action('Dashboard\MemberController@index')->with('success', 'Member updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();

        return redirect()
                ->route('admin.members.index')
                ->with('success', 'Member deleted!');
    }
}
