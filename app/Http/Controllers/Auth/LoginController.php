<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use App\User;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        Cookie::queue(
            Cookie::forget('tml-cookie')
        ) ;

        if ($request->wantsJson()) {
            return response()->json([], 204);
        }
    }

	public function redirectToProvider()
	{
		return Socialite::driver('google')->stateless()->redirect();
	}

	public function handleProviderCallback()
	{
		try {
			$user = Socialite::driver('google')->stateless()->user();
		} catch (Exception $e) {

			return redirect('/login');
		}

		if (explode("@", $user->email)[1] !== 'corp.web4pro.com.ua') {

			return redirect()->to('/login');
		}

		$existing_user = User::where('email', $user->email)->first();

		if($existing_user){

			Auth::login($existing_user);

			return redirect('login/google');
        }else {

			$new_user = User::create([
				'name'              => $user->name,
				'email'             => $user->email,
				'email_verified_at' => now(),
				'password'          => password_hash( Str::random(10), PASSWORD_BCRYPT),
				'remember_token'    => Str::random(10),
				'api_token'         => Str::random(60),
			]);

			Auth::login($new_user);

			$manager_role_id = Role::where('name', 'member')->first()->id;
			$new_user->roles()->attach($manager_role_id);

			return redirect()->back();
		}
	}
}
