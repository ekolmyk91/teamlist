<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\OffTimeType;

class OffTimeTypesController extends Controller
{
    public function index()
    {
        $types = OffTimeType::all();

        return response()->json($types);
    }
}
