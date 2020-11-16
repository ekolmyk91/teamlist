<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Position;

class PositionController extends Controller
{
    public function index()
    {

        $positions = Position::all();

        return response()->json($positions);
    }

    public function show($id)
    {
        $position = Position::find($id);

        return response()->json($position);
    }
}
