<?php

namespace App\Http\Controllers\Dashboard;

use App\Calendar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CalendarController extends Controller
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
        $calendars = Calendar::paginate(30);

        return view('dashboard.calendar.index', compact('calendars'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Calendar $calendar
     * @return \Illuminate\Http\Response
     */
    public function edit(Calendar $calendar)
    {
        return view('dashboard.calendar.edit', compact('calendar'));
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
            'is_weekday' => 'nullable|string',
            'is_holiday' => 'nullable|string'
        ]);
        $calendar = Calendar::find($id);

        if (empty($calendar)) {

            return back()
                ->withErrors(['msg' => "id = [{$id}]  not found"])
                ->withInput();
        }

        $calendar->is_weekday = $request->get('is_weekday') ? 1 : 0;
        $calendar->is_holiday = $request->get('is_holiday') ? 1 : 0;
        $calendar->save();

        return redirect()
            ->route('admin.calendar.index')
            ->with('success', 'Date updated!');
    }
}
