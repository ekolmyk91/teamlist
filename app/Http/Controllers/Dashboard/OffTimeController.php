<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Requests\StoreAdminOffTimeRequest;
use App\Http\Requests\UpdateAdminOffTimeRequest;
use App\Mail\MemberNewOffTime;
use App\Mail\MemberUpdatedOffTime;
use App\Member;
use App\OffTime;
use App\OffTimeType;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

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
            ->orderByRaw('FIELD(status , "'.WAITING_APPROVE_OFF_TIME_STATUS.'") DESC')
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
     * @param StoreAdminOffTimeRequest $request
     * @return RedirectResponse
     */
    public function store(StoreAdminOffTimeRequest $request)
    {
        $offTime = OffTime::query()->create($request->validated());
        $message = array( 'type' => 'errors', 'msg' => 'Problem with saving. Try again.' );

        if (!empty($offTime) && $offTime instanceof OffTime) {

            Mail::to(Member::find($offTime->user_id)->email)->send(new MemberNewOffTime($offTime));
            $message = array( 'type' => 'success', 'msg' => 'Off-Time was saved!' );
        }

        return redirect()
            ->route('admin.off_time.index')
            ->with($message['type'], $message['msg']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  OffTime $offTime
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function edit(OffTime $offTime)
    {
        $types    = OffTimeType::all();
        $users    = Member::select('user_id', 'name', 'surname')->get();
        $statuses = config('constants.off_time_status');

        return view('dashboard.off_time.edit', compact('offTime', 'types', 'users', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAdminOffTimeRequest $request
     * @param OffTime $offTime
     * @return RedirectResponse
     */
    public function update(UpdateAdminOffTimeRequest $request, OffTime $offTime)
    {
        $message = array( 'type' => 'errors', 'msg' => 'Problem with updating. Try again.' );

        if ( $offTime->update($request->validated()) ) {

            Mail::to(Member::find($offTime->user_id)->email)->send(new MemberUpdatedOffTime($offTime));
            $message = array( 'type' => 'success', 'msg' => 'Off-Time was updated!' );
        }

        return redirect()
            ->route('admin.off_time.index')
            ->with($message['type'], $message['msg']);
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
            return back()->withErrors(['msg' => 'Delete error.']);
        }

        return redirect()->action('Dashboard\OffTimeController@index')->with('success', 'Off-Time Item was deleted!');
    }
}
