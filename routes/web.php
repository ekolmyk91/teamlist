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

Route::get('/', function () {
    return view('welcome');
})->name('front');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/404', 'DefaultController@notFound');

Route::group(['middleware' => ['auth']], function (){
    Route::get('/member', 'DemoController@memberDemo')->name('member');

    Route::group(['middleware' => ['admin']], function (){
        Route::prefix('admin')->group(function(){
            Route::get('/', 'Dashboard\AdminController@index')->name('dashboard');
            Route::get('/members', 'Dashboard\AdminController@index')->name('admin_members');
        });
    });

});
