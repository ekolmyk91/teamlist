<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['register' => false]);
//Auth::routes();

Route::get('/404', 'DefaultController@notFound');

Route::group(['middleware' => ['auth']], function (){
    Route::get('/member', 'DemoController@memberDemo')->name('member');

    Route::group(['middleware' => ['admin']], function (){
        Route::prefix('admin')->name('admin.')->group(function(){
            Route::get('/', 'Dashboard\AdminController@index')->name('dashboard');
            Route::resource('members', 'Dashboard\MemberController');
            Route::resource('solutions', 'Dashboard\SolutionController');
            Route::resource('departments', 'Dashboard\DepartmentController');
            Route::resource('categories', 'Dashboard\CategoryController');
            Route::resource('tags', 'Dashboard\TagController');
            Route::resource('skills', 'Dashboard\SkillController');
            Route::resource('positions', 'Dashboard\PositionController');
            Route::resource('certificates', 'Dashboard\CertificateController');
            Route::resource('links', 'Dashboard\LinkController');
	        Route::get('/search', 'Dashboard\MemberController@search')->name('members.search');
        });
    });

});

Route::get('login/google', 'Auth\LoginController@redirectToProvider');
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');

Route::view('/{path?}', 'app')->middleware('auth', 'apiToken');
