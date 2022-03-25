<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->except('logout');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $links = Link::all();

        return view('dashboard.link.index', ['links' => $links]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.link.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request->validate([
            'title'        => 'required|string|min:1|max:200',
            'url'          => 'required|url',
            'icon'         => 'nullable|string|min:2|max:40',
            'order_number' => 'required|numeric|min:1|max:999|unique:links',
        ]);

        $link = new Link([
            'title'        => $request->get('title'),
            'url'          => $request->get('url'),
            'icon'         => $request->get('icon'),
            'order_number' => $request->get('order_number')
        ]);

        $link->save();

        return redirect()->action('Dashboard\LinkController@index')
            ->with('success', 'Link saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Link  $link
     * @return \Illuminate\Http\Response
     */
    public function show(Link $link)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $link = Link::find($id);

        return view('dashboard.link.edit', ['link' => $link]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'title'        => 'required|string|min:1|max:200',
            'url'          => 'required|url',
            'icon'         => 'nullable|string|min:2|max:40',
            'order_number' => 'required|numeric|min:1|max:999|unique:links,order_number,' . $id,
        ]);

        $link = Link::find($id);

        $link->update([
            'title'        => $request->get('title'),
            'url'          => $request->get('url'),
            'icon'         => $request->get('icon'),
            'order_number' => $request->get('order_number')
        ]);

        return redirect()->action('Dashboard\LinkController@index')->with('success', 'Link updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $link = Link::find($id);

        try {
            $link->delete();
        }
        catch(\Illuminate\Database\QueryException $ex) {
            return back()
                ->withErrors(['msg' => 'Delete error.']);
        }

        return redirect()->action('Dashboard\LinkController@index')->with('success', 'Link deleted!');
    }
}
