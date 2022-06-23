<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Calendar;

class CalendarController extends Controller
{
    public function show($year)
    {
        $calendar = Calendar::where('year', '=', $year)->get();

        return response()->json($calendar);
    }
}
