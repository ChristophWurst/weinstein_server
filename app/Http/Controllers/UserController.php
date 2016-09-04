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
use Exception;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class UserController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $viewFactory;

	public function __construct(MasterDataStore $masterDataStore, AuthManager $auth, Factory $viewFactory) {
		$this->masterDataStore = $masterDataStore;
		$this->auth = $auth;
		$this->viewFactory = $viewFactory;
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
		return $this->viewFactory->make('settings/user/index', [
				'users' => $users
		]);
	}

	/**
	 * Show form for creating a new user
	 * @return Response
	 */
	public function create() {
		$this->authorize('create-user');

		return $this->viewFactory->make('settings/user/form');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * @return Response
	 */
	public function store(Request $request) {
		$this->authorize('create-user');

		$data = $request->all();

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

		return $this->viewFactory->make('settings/user/show', [
				'data' => $user,
		]);
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

		return $this->viewFactory->make('settings/user/form', [
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
	public function update(User $user, Request $request) {
		$this->authorize('edit-user', $user);

		$data = $request->all();
		// convert admin value to boolean
		$data['admin'] = (isset($data['admin']) && $data['admin'] === 'true');


		// prevent admins from removing their own admin privileges
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

		return $this->viewFactory->make('settings/user/delete', [
				'user' => $user
		]);
	}

	/**
	 * Remove the specified user from storage.
	 *
	 * @param User $user        	
	 * @return Response
	 */
	public function destroy(User $user, Request $request) {
		$this->authorize('delete-user', $user);

		//prevent user from deleting her/his own account
		if ($user->is($this->auth->user())) {
			new Exception('users must not delete themselves');
		}

		if ($request->get('del') === 'Ja') {
			$this->masterDataStore->deleteUser($user);
		}
		return Redirect::route('settings.users');
	}

}
