<?php

use App\Http\Controllers\API\RequestController;
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


Route::group(['middleware' => ['auth:api']], function (){
	Route::get('user/current', 'API\CommonController@getCurrentUser');
    Route::get('members', 'API\MemberController@index');
    Route::get('members/{id}', 'API\MemberController@show');

    Route::get('solutions', 'API\SolutionController@index');
    Route::get('solutions/{id}', 'API\SolutionController@show');

    Route::get('departments', 'API\DepartmentController@index');
    Route::get('departments/{id}', 'API\DepartmentController@show');

    Route::get('positions', 'API\PositionController@index');
    Route::get('positions/{id}', 'API\PositionController@show');

    Route::get('links', 'API\LinkController@index');

    Route::get('calendar/{year}', 'API\CalendarController@show');

    /**
     * Request from employees
     */
    Route::post('/time_off_request', [RequestController::class, 'timeOffRequest'])->name('timeOffRequest');
});
