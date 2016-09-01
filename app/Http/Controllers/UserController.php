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

use App\Contracts\MasterDataStore;
use App\Exceptions\ValidationException;
use App\Http\Controllers\BaseController;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use function view;

class UserController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var AuthManager */
	private $auth;

	public function __construct(MasterDataStore $masterDataStore, AuthManager $auth) {
		$this->masterDataStore = $masterDataStore;
		$this->auth = $auth;
	}

	/**
	 * Display a list of (all) users.
	 * - Admin sees all
	 * - Standard user sees only him/herself
	 * 
	 * @todo list linked applicant/associations
	 * @return Response
	 */
	public function index() {
		$this->authorize('list-users');

		$user = $this->auth->user();
		$users = $this->masterDataStore->getUsers($user);
		return view('settings/user/index')->with('users', $users);
	}

	/**
	 * Show form for creating a new user
	 * @return Response
	 */
	public function create() {
		$this->authorize('create-user');

		return view('settings/user/form');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store() {
		$this->authorize('create-user');

		$data = Input::all();

		//convert admin value to boolean
		if (isset($data['admin'])) {
			$data['admin'] = $data['admin'] === 'true';
		}

		try {
			$this->masterDataStore->createUser($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.users/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		//Log::info('user <' . $data['username'] . '> created by ' . $this->auth->user()->username);
		return Redirect::route('settings.users');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param User $user        	
	 * @return Response
	 */
	public function show(User $user) {
		$this->authorize('show-user', $user);

		return view('settings/user/show')->with('data', $user);
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
		$this->authorize('edit-user', $user);

		return view('settings/user/form')->with([
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
		$this->authorize('edit-user', $user);

		$data = Input::all();
		// convert admin value to boolean
		$data['admin'] = (isset($data['admin']) && $data['admin'] === 'true');


		// prevent admin from removing her/his own admin privileges
		if ($this->auth->user()->username === $user->username) {
			unset($data['admin']);
		}

		// do not change password if it was left blank
		if (isset($data['password']) && $data['password'] === '') {
			unset($data['password']);
		}

		try {
			$this->masterDataStore->updateUser($user, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.users/edit', [
						'user' => $user->username
					])
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
		$this->authorize('delete-user', $user);

		return view('settings/user/delete')->with('user', $user);
	}

	/**
	 * Remove the specified user from storage.
	 *
	 * @param User $user        	
	 * @return Response
	 */
	public function destroy(User $user) {
		$this->authorize('delete-user', $user);

		//prevent user from deleting her/his own account
		if ($user->username === $this->auth->user()->username) {
			App::abort(500);
		}

		if (Input::get('del') == 'Ja') {
			$this->masterDataStore->deleteUser($user);
		}
		Log::info('user <' . $user->username . '> deleted by ' . $this->auth->user()->username);
		return Redirect::route('settings.users');
	}

}
