<?php

namespace App\Http\Controllers\Dashboard;

use App\OffTimeType;
use App\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OffTimeTypeController extends Controller
{

    public function __construct()
    {
        $this->middleware('admin')->except('logout');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        $types = OffTimeType::all();

        return view('dashboard.off_time_type.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        return view('dashboard.off_time_type.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:off_time_types|min:2|max:50',
        ]);

        $type = new OffTimeType([
            'name' => $request->get('name'),
        ]);
        $type->save();

        return redirect()
            ->route('admin.off_time_type.index')
            ->with('success', 'Type saved!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\OffTimeType $offTimeType
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(OffTimeType $offTimeType)
    {
        if (!$offTimeType) {
            abort (404);
        }
        return view('dashboard.off_time_type.edit', compact('offTimeType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'=>'required|unique:off_time_types|min:2|max:50',
        ]);
        $type = OffTimeType::find($id);

        if (empty($type)) {

            return back()
                ->withErrors(['msg' => "id = [{$id}]  not found"])
                ->withInput();
        }

        $type->name =  $request->get('name');
        $type->save();

        return redirect()
            ->route('admin.off_time_type.index')
            ->with('success', 'Type updated!');
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $type = OffTimeType::find($id);

        try {
            $type->delete();
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return back()
                ->withErrors(['msg' => 'Delete error.  Possibly used in another table.']);
        }

        return redirect()
            ->route('admin.off_time_type.index')
            ->with('success', 'Type deleted!');
    }
}
