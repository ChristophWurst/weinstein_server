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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class AuthenticationController extends BaseController {

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
	 * @return Response
	 */
	public function auth() {
		if (Auth::attempt(['username' => Input::get('username'), 'password' => Input::get('password')], true)) {
			return Redirect::route('account');
		}

		Session::forget('successful');
		Session::put('successful', false);
		return Redirect::route('login')
				->withInput();
	}

	/**
	 * Log user out
	 *
	 * @return Response
	 */
	public function logout() {
		if (Auth::check()) {
			Auth::logout();
		}
		return Redirect::route('start');
	}

}
