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
        $position = new Position([
            'name' => $request->get('name'),
        ]);
        $position->save();

        return redirect()
                ->route('admin.positions.index')
                ->with('success', 'Position saved!');
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'=>'required|unique:positions|min:2|max:50',
        ]);
        $position = Position::find($id);

        if (empty($position)) {

            return back()
                ->withErrors(['msg' => "id = [{$id}]  not found"])
                ->withInput();
        }

        $position->name =  $request->get('name');
        $position->save();

            return redirect()
                ->route('admin.positions.index')
                ->with('success', 'Position updated!');
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $position = Position::find($id);

        try {
            $position->delete();
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return back()
                ->withErrors(['msg' => 'Delete error.  Possibly used in another table.']);
        }

        return redirect()
            ->route('admin.positions.index')
            ->with('success', 'Position deleted!');
    }
}
