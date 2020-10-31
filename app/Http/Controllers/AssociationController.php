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
use App\MasterData\Association;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class AssociationController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $view;

	/**
	 * @param MasterDataStore $masterDataStore
	 * @param AuthManager $auth
	 * @param Factory $viewFactory
	 */
	public function __construct(MasterDataStore $masterDataStore, AuthManager $auth, Factory $viewFactory) {
		$this->masterDataStore = $masterDataStore;
		$this->auth = $auth;
		$this->view = $viewFactory;
	}

	/**
	 * Display a listing of associations
	 * 
	 * - admin may see all
	 * - other users see only administrated ones
	 *
	 * @return View
	 */
	public function index() {
		$user = $this->auth->user();
		$associations = $this->masterDataStore->getAssociations($user);
		return $this->view->make('settings/association/index', [
				'associations' => $associations
		]);
	}

	/**
	 * Show the form for creating a new association
	 *
	 * @return View
	 */
	public function create() {
		$this->authorize('create-association');

		$users = $this->masterDataStore->getUsers()->pluck('username', 'username')->all();
		return $this->view->make('settings/association/form', [
				'users' => $this->selectNone + $users,
		]);
	}

	/**
	 * Validate and store new association
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function store(Request $request) {
		$this->authorize('create-association');

		$data = $request->all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && (empty($data['wuser_username']) || $data['wuser_username'] === 'none')) {
			unset($data['wuser_username']);
		}
		try {
			$this->masterDataStore->createAssociation($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.associations/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.associations');
	}

	/**
	 * Show specified associatoin
	 * 
	 * @param Association $association
	 * @return View
	 */
	public function show(Association $association) {
		$this->authorize('show-association', $association);

		return $this->view->make('settings/association/show', [
				'data' => $association,
		]);
	}

	/**
	 * Show the form for editing the specified association
	 *
	 * redirect if user is neither maintainer nor admin
	 * 
	 * @param  Association $association
	 * @return View
	 */
	public function edit(Association $association) {
		$this->authorize('edit-association', $association);

		$user = $this->auth->user();
		if ($user->isAdmin()) {
			$users = $this->selectNone + $this->masterDataStore->getUsers()->pluck('username', 'username')->all();
		} else {
			$users = $user->pluck('username', 'username')->all();
		}
		return $this->view->make('settings/association/form', [
				'data' => $association,
				'users' => $users,
		]);
	}

	/**
	 * Update the specified association in storage
	 *
	 * @param Association $association
	 * @param Request $request
	 * @return Response
	 */
	public function update(Association $association, Request $request) {
		$this->authorize('edit-association', $association);

		$data = $request->all();
		// remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			$data['wuser_username'] = null;
		}
		// only admin can change id
		if (isset($data['id']) && !$this->auth->user()->isAdmin()) {
			unset($data['id']);
		}
		// only admin can change user
		if (isset($data['wuser_username']) && !$this->auth->user()->isAdmin()) {
			unset($data['wuser_username']);
		}

		try {
			$this->masterDataStore->updateAssociation($association, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.associations/edit', ['association' => $association->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.associations');
	}

}
