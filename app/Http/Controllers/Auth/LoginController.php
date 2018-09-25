<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\ActivityLogger;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class LoginController extends BaseController
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

	// use AuthenticatesUsers;

	/** @var ActivityLogger */
	private $activityLogger;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $view;

	/**
	 * Where to redirect users after login.
	 *
	 * @var string
	 */
	protected $redirectTo = '/home';

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(AuthManager $auth, ActivityLogger $activityLogger, Factory $view)
	{
		$this->middleware('guest', ['except' => 'logout']);

		$this->auth = $auth;
		$this->activityLogger = $activityLogger;
		$this->view = $view;
	}

	public function login()
	{
		return $this->view->make('account/login');
	}

	/**
	 * Try to log user in
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function auth(Request $request) {
		$username = $request->input('username');
		$password = $request->input('password');

		if ($this->auth->attempt([
			'username' => $username,
			'password' => $password
		], true)) {
			$this->activityLogger->logUserAction('hat sich am System angemeldet', $this->auth->user());
			return Redirect::route('account');
		}

		Session::forget('successful');
		Session::put('successful', false);
		return Redirect::route('login')->withInput();
	}

	/**
	 * Display account information
	 *
	 * @return Response
	 */
	public function account()
	{
		return $this->view->make('account/account');
	}

	/**
	 * Log user out
	 *
	 * @return Response
	 */
	public function logout()
	{
		if ($this->auth->check()) {
			$user = $this->auth->user();
			$this->auth->logout();
			$this->activityLogger->logUserAction('hat sich vom System abgemeldet', $user);
		}
		return Redirect::route('start');
	}
}
