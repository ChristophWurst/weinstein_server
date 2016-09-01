<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License,version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace App\Http\Controllers;

use App\Contracts\ActivityLogger;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class AuthenticationController extends BaseController {

	/** @var ActivityLogger */
	private $activityLogger;

	/** @var AuthManager */
	private $auth;

	public function __construct(AuthManager $auth, ActivityLogger $activityLogger) {
		$this->auth = $auth;
		$this->activityLogger = $activityLogger;
	}

	/**
	 * Display account information
	 *
	 * @return Response
	 */
	public function account() {
		return View::make('account/account');
	}

	/**
	 * Show login form
	 *
	 * @return Response
	 */
	public function login() {
		return View::make('account/login');
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
	 * Log user out
	 *
	 * @return Response
	 */
	public function logout() {
		if (Auth::check()) {
			$user = $this->auth->user();
			Auth::logout();
			$this->activityLogger->logUserAction('hat sich vom System abgemeldet', $user);
		}
		return Redirect::route('start');
	}

}
