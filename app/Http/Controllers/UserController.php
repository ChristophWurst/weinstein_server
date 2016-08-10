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
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use UserHandler;
use App\User;
use App\Http\Controllers\BaseController;
use Weinstein\Exception\ValidationException;

class UserController extends BaseController {

	/**
	 * Filter user administrates user
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterAdministrates($route, $request) {
		$user = Route::input('user');

		if (!$user->administrates(Auth::user())) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->beforeFilter('auth');
		$this->beforeFilter('@filterAdmin', [
		    'only' => [
			'create',
			'store',
			'delete',
			'destroy',
		    ],
		]);
		$this->beforeFilter('@filterAdministrates', [
		    'only' => [
			'edit',
			'update',
			'show',
		    ],
		]);
	}

	/**
	 * Display a list of (all) users.
	 * - Admin sees all
	 * - Standard user sees only himself
	 * 
	 * @return Response
	 * 
	 * TODO: list linked applicant/associations
	 */
	public function index() {
		return View::make('settings/user/index')->with('users', UserHandler::getUsersUsers(Auth::user()));
	}

	/**
	 * Show form for creating a new user
	 * @return Response
	 */
	public function create() {
		return View::make('settings/user/form');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$data = Input::all();

		//convert admin value to boolean
		if (isset($data['admin']) && $data['admin'] === 'true') {
			$data['admin'] = true;
		} else {
			$data['admin'] = false;
		}

		try {
			UserHandler::create($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.users/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		Log::info('user <' . $data['username'] . '> created by ' . Auth::user()->username);
		return Redirect::route('settings.users');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id        	
	 * @return Response
	 */
	public function show(User $user) {
		return View::make('settings/user/show')->with('data', $user);
	}

	/**
	 * Show the form for editing the specified resource.
	 * 
	 * redirects if user not found
	 *  or if not admin and not same user
	 *
	 * @param User $user        	
	 * @return Response
	 */
	public function edit(User $user) {
		return View::make('settings/user/form')->with([
			    'data' => $user
		]);
	}

	/**
	 * Update the specified user in storage.
	 *
	 * validates data
	 * redirects if user not found
	 *  or if not admin and not same user
	 * 
	 * @param User $user        	
	 * @return Response
	 */
	public function update(User $user) {
		$data = Input::all();

		//convert admin value to boolean
		if (isset($data['admin']) && $data['admin'] === 'true') {
			$data['admin'] = true;
		} else {
			$data['admin'] = false;
		}

		try {
			UserHandler::update($user, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.users/edit', ['user' => $user->username])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.users');
	}

	/**
	 * Let the user confirm or abort the deletion
	 *
	 * redirect if
	 *  - user does not exist
	 *  - user tries to delete himself
	 * 
	 * @param User $user
	 * @return Response
	 */
	public function delete(User $user) {
		return View::make('settings/user/delete')->with('user', $user);
	}

	/**
	 * Remove the specified user from storage.
	 *
	 * @param User $user        	
	 * @return Response
	 */
	public function destroy(User $user) {
		if (Input::get('del') == 'Ja') {
			UserHandler::delete($user);
		}
		Log::info('user <' . $user->username . '> deleted by ' . Auth::user()->username);
		return Redirect::route('settings.users');
	}

}
