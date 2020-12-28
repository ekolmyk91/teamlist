<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['middleware' => ['check.key']], function (){
    Route::get('members', 'API\MemberController@index');
    Route::get('members/{id}', 'API\MemberController@show');

    Route::get('solutions', 'API\SolutionController@index');
    Route::get('solutions/{id}', 'API\SolutionController@show');

    Route::get('departments', 'API\DepartmentController@index');
    Route::get('departments/{id}', 'API\DepartmentController@show');

    Route::get('positions', 'API\PositionController@index');
    Route::get('positions/{id}', 'API\PositionController@show');
});
