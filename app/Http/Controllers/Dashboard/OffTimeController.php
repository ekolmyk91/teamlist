<?php

namespace App\Http\Controllers\Dashboard;

use App\Member;
use App\OffTime;
use App\OffTimeType;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OffTimeController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $offTimeList = OffTime::with('member', 'offTimeType')
            ->orderByRaw('FIELD(status , "'.WAITING_APPROVE_STATUS.'") DESC')
            ->orderByDesc('status')
            ->orderBy('start_day')
            ->paginate(10);

        return view('dashboard.off_time.index', ['offTimeList' => $offTimeList]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $types    = OffTimeType::all();
        $users    = Member::select('user_id', 'name', 'surname')->get();
        $statuses = config('constants.off_time_status');

        return view('dashboard.off_time.create', compact('types', 'users', 'statuses'));
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
            'start_day' => 'required|date',
            'end_day'   => 'required|date',
            'user'      => 'required|exists:members,user_id',
            'type'      => 'required|exists:off_time_types,id',
            'status'    => 'required|in:'. implode(',', config('constants.off_time_status'))
        ]);

        $offTimeItem = new OffTime([
            'start_day' => $request->get('start_day'),
            'end_day'   => $request->get('end_day'),
            'user_id'   => $request->get('user'),
            'type_id'   => $request->get('type'),
            'status'    => $request->get('status'),
        ]);
        $offTimeItem->save();

        return redirect()
            ->route('admin.off_time.index')
            ->with('success', 'Off-Time Item saved!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  OffTime $offTime
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(OffTime $offTime)
    {
        if (!$offTime) {
            abort (404);
        }

        $types    = OffTimeType::all();
        $users    = Member::select('user_id', 'name', 'surname')->get();
        $statuses = config('constants.off_time_status');

        return view('dashboard.off_time.edit', compact('offTime', 'types', 'users', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, OffTime $offTime)
    {
        $request->validate([
            'start_day' => 'required|date',
            'end_day'   => 'required|date',
            'user'      => 'required|exists:members,user_id',
            'type'      => 'required|exists:off_time_types,id',
            'status'    => 'required|in:'. implode(',', config('constants.off_time_status'))
        ]);

        $offTime->update($request->all());

        return redirect()
            ->route('admin.off_time.index')
            ->with('success', 'Off-Time Item saved!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $offTimeItem = OffTime::find($id);

        try {
            $offTimeItem->delete();
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return back()
                ->withErrors(['msg' => 'Delete error.']);
        }

        return redirect()->action('Dashboard\OffTimeController@index')->with('success', 'Off Time Item deleted!');
    }
}