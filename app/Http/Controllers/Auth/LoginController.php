<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
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
//		try {

			$user = Socialite::driver('google')->stateless()->user();

			$finduser = User::where('google_id', $user->id)->first();
//
			if($finduser){
//
				Auth::login($finduser);
//
				return redirect('/');
//
            }else{
				$newUser = User::create([
					'name' => $user->name,
					'email' => $user->email,
					'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
					'google_id'=> $user->id
				]);
//
				Auth::login($newUser);
//
				return redirect()->back();
			}
//
//		} catch (Exception $e) {
//			echo $e->getMessage();
//			return redirect('auth/google');
//		}
	}
}
