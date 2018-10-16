<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Request;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

	protected function rules()
	{
		return [
			'token' => 'required',
			'username' => 'required|min:4|max:80|alpha_dash',
			'password' => 'required|confirmed|min:5|max:80',
		];
	}

	protected function credentials(Request $request)
	{
		return $request->only(
			'username', 'password', 'password_confirmation', 'token'
		);
	}

	protected function sendResetFailedResponse(Request $request, $response)
	{
		return redirect()->back()
			->withInput($request->only('email'))
			->withErrors(['username' => trans($response)]);
	}

	protected function resetPassword($user, $password)
	{
		$user->forceFill([
			'password' => $password,
			'remember_token' => Str::random(60),
		])->save();

		$this->guard()->login($user);
	}

}
