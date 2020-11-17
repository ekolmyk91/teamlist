<?php

namespace App\Http\Controllers\Dashboard;

use App\Certificate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CertificateController extends Controller
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
        $certificates = Certificate::all();

        return view('dashboard.certificate.index', compact('certificates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.certificate.create');
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
            'name'=>'required|unique:certificates|min:4|max:120',
            'logo'    =>'required|image|mimes:png|max:512',
        ]);
        $certificate = new Certificate([
            'name' => $request->get('name'),
        ]);

        $logo = $request->file('logo');
        $filename = time() . '-' . $logo->getClientOriginalName();

        request()->file('logo')->storeAs('logo', $filename);
        $certificate['logo'] = $filename;

        $certificate->save();

        return redirect()
            ->route('admin.certificates.index')
            ->with('success', 'Certificate saved!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Position $certificate
     * @return \Illuminate\Http\Response
     */
    public function edit(Certificate $certificate)
    {
        if (!$certificate) { abort (404); }
        return view('dashboard.certificate.edit', compact('certificate'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
