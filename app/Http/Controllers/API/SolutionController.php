<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Solution;
use Image;

class SolutionController extends Controller
{
    public function index()
    {
        $solutions = Solution::all();

        return response()->json($solutions);
    }

    public function show($id)
    {
        $solution = Solution::find($id);

        return response()->json($solution);
    }
}
