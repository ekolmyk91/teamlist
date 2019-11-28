<?php

namespace App\Http\Controllers\Dashboard;

use App\Category;
use App\Solution;
use App\Http\Controllers\Controller;
use App\Tag;
use Illuminate\Http\Request;

class SolutionController extends Controller
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
        $solutions = Solution::all();

        return view('dashboard.solution.index', ['solutions' => $solutions]);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $categories = Category::all();
        $tags = Tag::all();

        return view('dashboard.solution.create', [
          'categories' => $categories,
          'tags' => $tags,
        ]);
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
          'title'=>'required',
        ]);

//        if($request->get('tags')){
//            $tag = Tag::find();
//        }


        //Create Solution entity.
        $solution = new Solution([
          'title' => $request->get('title'),
          'content' => $request->get('content'),
          'author' => \Auth::user()->id,
          'archive' => $request->get('archive'),
          'active' => $request->get('active'),
        ]);
        $solution->save();


        return redirect()->action('Dashboard\SolutionController@index')->with('success', 'Solution saved!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $user_id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $solution      = Solution::find($id);

        return view('dashboard.solution.edit', [
          'solution'      => $solution,
        ]);
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
            'title' => 'required',
        ]);

        $solution = Solution::find($id);
        $solution->update($request->all());

        return redirect()->action('Dashboard\SolutionController@index')->with('success', 'Solution updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $solution = Solution::find($id);
        $solution->delete();

        return redirect()->action('Dashboard\SolutionController@index')->with('success', 'Solution deleted!');
    }
}
