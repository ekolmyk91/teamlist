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
            Route::patch('members/{member}/coffee', 'Dashboard\MemberController@toggleCoffee')->name('members.coffee.toggle');
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

            Route::get('coffee', 'Dashboard\CoffeeController@index')->name('coffee.index');
            Route::post('coffee/generate', 'Dashboard\CoffeeController@generate')->name('coffee.generate');
            Route::post('coffee/top-up', 'Dashboard\CoffeeController@topUp')->name('coffee.topUp');
            Route::post('coffee/meetings', 'Dashboard\CoffeeController@storeMeeting')->name('coffee.meetings.store');
            Route::delete('coffee/meetings/{meeting}', 'Dashboard\CoffeeController@destroyMeeting')->name('coffee.meetings.destroy');
            Route::patch('coffee/meetings/{meeting}/status', 'Dashboard\CoffeeController@updateStatus')->name('coffee.meetings.status');
            Route::get('coffee/settings', 'Dashboard\CoffeeController@settings')->name('coffee.settings');
            Route::put('coffee/settings', 'Dashboard\CoffeeController@updateSettings')->name('coffee.settings.update');
            Route::get('coffee/guide', 'Dashboard\CoffeeController@guide')->name('coffee.guide');
        });
    });

});

Route::get('login/google', 'Auth\LoginController@redirectToProvider');
Route::get('login/google/callback', 'Auth\LoginController@handleProviderCallback');

// Public signed links from the Random Coffee bot ("did the meeting happen?").
Route::get('coffee/confirm/{meeting}/{user}/{answer}', 'CoffeeConfirmationController')
    ->name('coffee.confirm')
    ->middleware('signed');

// Telegram webhook for the Random Coffee bot (non-local; secured by the
// X-Telegram-Bot-Api-Secret-Token header, CSRF-exempt — see VerifyCsrfToken).
Route::post('coffee/telegram/webhook', 'CoffeeTelegramWebhookController')
    ->name('coffee.telegram.webhook');

Route::view('/{path?}', 'app')->middleware('auth', 'apiToken');
