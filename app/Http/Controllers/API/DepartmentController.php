<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::all();

        return response()->json($departments);
    }

    public function show($id)
    {
        $department = Department::find($id);

        return response()->json($department);
    }
}
