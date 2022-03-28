<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Link;

class LinkController extends Controller
{
    public function index()
    {
        $links = Link::orderBy('order_number')->get();

        return response()->json($links);
    }
}
