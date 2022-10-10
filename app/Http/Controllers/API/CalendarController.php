<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Calendar;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CalendarController extends Controller
{
    public function showYear($year)
    {
       $calendar = Calendar::where('year', '=', $year)->get();

        return response()->json($calendar);
    }

    public function showMonth($year, $month)
    {
        $calendar = Calendar::where('year', '=', $year)->where('month', '=', $month)->get();

        return response()->json($calendar);
    }
}
