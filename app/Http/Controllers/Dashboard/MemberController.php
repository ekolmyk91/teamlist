<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Member;
use App\Department;
use App\User;
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
    public function index()
    {
        $members = Member::all();

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

        return view('dashboard.member.create', ['departments' => $departments]);
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
          'name'=>'required',
          'surname'=>'required',
          'email'=>'required'
        ]);


        //@TODO: user create
        $user = new User([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => User::generatePassword()
        ]);
        $user->save();

        $member = new Member([
          'name' => $request->get('name'),
          'surname' => $request->get('surname'),
          'email' => $request->get('email'),
          'phone_1' => $request->get('phone_1'),
          'phone_2' => $request->get('phone_2'),
          'birthday' => $request->get('birthday'),
          'about' => $request->get('about'),
          'user'  => $user
        ]);

        $member->save();

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

        return view('dashboard.member.edit', [
          'member'      => $member,
          'departments' => $departments,
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
