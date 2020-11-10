<?php

namespace App\Http\Controllers\Dashboard;

use App\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PositionController extends Controller
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
        $positions = Position::all();

        return view('dashboard.position.index', compact('positions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.position.create');
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
            'name'=>'required|unique:positions|min:2|max:50',
        ]);

        $data = $request->input();
        $position = (new Position())->create($data);

        if ($position) {

            return redirect()
                ->route('admin.positions.index')
                ->with('success', 'Position saved!');
        } else {

            return back()
                ->withErrors('msg', 'Save error')
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Position $position
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {
        if (!$position) { abort (404); }
        return view('dashboard.position.edit', compact('position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Position $position
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Position $position)
    {
        $request->validate([
            'name'=>'required|unique:positions|min:2|max:50',
        ]);

        if (empty($position)) {
            return back()
                ->withErrors('msg', "id = [$position->id] not found")
                ->withInput();
        }

        $data = $request->all();
        $result = $position->update($data);

        if ($result) {
            return redirect()
                ->route('admin.positions.index')
                ->with('success', 'Position updated!');
        } else {
            return back()
                ->withErrors('msg', 'Save error')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param  \App\Position $position
     * @return \Illuminate\Http\Response
     */
    public function destroy(Position $position)
    {
        $position->delete();

        if ($position) {

            return redirect()
                ->route('admin.positions.index')
                ->with('success', 'Position deleted!');
        } else {

            return back()
                ->withErrors('msg', 'Save error');
        }
    }
}
