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
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $request->validate([
          'name'           => 'nullable|string|min:2|max:20',
          'surname'        => 'nullable|string|min:2|max:40',
          'email'          => 'required|email|ends_with:corp.web4pro.com.ua|unique:users',
          'password'       => 'required|string|min:8',
          'birthday'       => 'nullable|string',
          'start_work_day' => 'nullable|date|before:tomorrow',
          'phone_1'        => 'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members',
          'phone_2'        => 'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members',
          'city'           => 'nullable|string|min:2|max:40',
          'department'     => 'nullable',
          'position'       => 'nullable',
          'certificates'   => 'nullable|array',
          'about'          => 'nullable|string|max:1000',
          'avatar'         => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
          'manager'        => 'nullable|string',
        ]);

        $active = $request->get('active');
        $userFields = [
          'name'     => $request->get('name'),
          'email'    => $request->get('email'),
          'password' => password_hash($request->get('password'), PASSWORD_BCRYPT),
          'active'   => isset($active) ? 1 : 0,
          'api_token' => Str::random(60),
        ];

        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $filename = time() . '-' . $avatar->getClientOriginalName();

			$avatar_resize = Image::make($avatar->getRealPath());
			$avatar_resize->fit( 245, null, null, 'top');

			$avatar_resize->save(storage_path('app/public/avatar/' . $filename), 100);
			$userFields['avatar'] = $filename;
        }

        //Create User entity.
        $user = new User($userFields);
        $user->save();

        //Save user role in pivot table.
        $role_ids[] = Role::where('name', 'member')->first()->id;
        if ($request->get('manager')) {
            $role_ids[] = Role::where('name', 'manager')->first()->id;
        }
        $user->roles()->attach($role_ids);


        $start_work_day = $request->get('start_work_day');
		$birthday       = $request->get('birthday');
        //Create Member entity and attach User.
        $member = new Member([
          'user_id'        => $user->id,
          'name'           => $request->get('name'),
          'surname'        => $request->get('surname'),
          'email'          => $request->get('email'),
          'phone_1'        => $request->get('phone_1'),
          'phone_2'        => $request->get('phone_2'),
          'city'           => $request->get('city'),
          'birthday'       => isset($birthday) ? Carbon::parse($request->get('birthday') . '/2020') : null,
          'start_work_day' => isset($start_work_day) ? Carbon::parse($start_work_day) : null,
          'about'          => $request->get('about'),
          'department_id'  => $request->get('department'),
          'position_id'    => $request->get('position'),
          'trainee'        => $request->get('trainee') ? 1 : 0,
          'user'           => $user
        ]);
        $member->save();
        if(!empty($request->certificate[0])) {
            $member->certificates()->sync($request->input('certificate', []));
        }

	    $url = $request->input('url');
        return Redirect::to($url)->with('success', 'Member saved!');
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
        $member       = Member::find($user_id);
        $departments  = Department::all();
        $positions    = Position::all();
        $certificates = Certificate::all()->pluck('name', 'id');
        $roles        = $member->user->roles()->pluck('name')->toArray();

        return view('dashboard.member.edit', [
          'member'       => $member,
          'departments'  => $departments,
          'positions'    => $positions,
          'certificates' => $certificates,
          'roles'        => $roles,
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
            'surname'        => 'nullable|string|min:2|max:40',
            'name'           => 'nullable|string|min:2|max:20',
            'email'          => 'required|email|ends_with:corp.web4pro.com.ua|unique:users,email,' . $id,
            'password'       => 'nullable|string|min:8',
            'birthday'       => 'nullable|string',
            'start_work_day' => 'nullable|date|before:tomorrow',
            'phone_1'        => 'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members,phone_1,' . $id .',user_id',
            'phone_2'        => 'nullable|regex:/^[0-9\-\+]{7,15}$/|unique:members,phone_2,' . $id .',user_id',
            'city'           => 'nullable|string|min:2|max:40',
            'department'     => 'nullable',
            'position'       => 'nullable',
            'certificates'   => 'nullable|array',
            'about'          => 'nullable|string|max:1000',
            'avatar'         => 'nullable|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'manager'        => 'nullable|string',
        ]);

        $member = Member::find($id);

        $active = $request->get('active');
        $userFields = [
            'name'   => $request->get('name'),
            'email'  => $request->get('email'),
            'active' => isset($active) ? 1 : 0,
            'api_token' => Str::random(60),
        ];

        if (!empty(request()->get('password'))) {
            $userFields['password'] = password_hash($request->get('password'), PASSWORD_BCRYPT);
        }

        if($request->hasFile('avatar')){
            $avatar = $request->file('avatar');
            $filename = time() . '-' . $avatar->getClientOriginalName();

			$avatar_resize = Image::make($avatar->getRealPath());
			$avatar_resize->fit( 245, null, null, 'top');

			$avatar_resize->save(storage_path('app/public/avatar/' . $filename), 100);
			$userFields['avatar'] = $filename;
        }

        $start_work_day = $request->get('start_work_day');
	    $birthday       = $request->get('birthday');


	    $member->user()->update($userFields);

        //Update user role (manager) in pivot table.
        $role_ids[] = Role::where('name', 'manager')->first()->id;
        if ($request->get('manager')) {
            $member->user->roles()->sync($role_ids, false);
        } else {
            $member->user->roles()->detach($role_ids);
        }

        $member->update([
            'name'           => $request->get('name'),
            'surname'        => $request->get('surname'),
            'email'          => $request->get('email'),
            'phone_1'        => $request->get('phone_1'),
            'phone_2'        => $request->get('phone_2'),
            'city'           => $request->get('city'),
            'birthday'       => isset($birthday) ? Carbon::parse($request->get('birthday') . '/2020') : null,
            'start_work_day' => isset($start_work_day) ? Carbon::parse($start_work_day) : null,
            'about'          => $request->get('about'),
            'department_id'  => $request->get('department'),
            'position_id'    => $request->get('position'),
	        'trainee'        => $request->get('trainee') ? 1 : 0
        ]);

        if(!empty($request->certificate[0])) {
            $member->certificates()->sync($request->input('certificate', []));
        } else {
            $member->certificates()->sync([]);
        }

        $url = $request->input('url');

        return Redirect::to($url)->with('success', 'Member updated!');
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
                ->back()
                ->with('success', 'Member deleted!');
    }

	public function search(Request $request)
	{
       $members =  Member::search($request->get('query'))->paginate(10);

        return view('dashboard.member.index', ['members' => $members]);
	}
}
