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

use App\Association;
use App\Http\Controllers\BaseController;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Route;
use Weinstein\Exception\ValidationException as ValidationException;
use Weinstein\Support\Facades\AssociationHandlerFacade as AssociationHandler;

class AssociationController extends BaseController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->middleware('auth');
	}

	/**
	 * Display a listing of associations
	 * 
	 * - admin may see all
	 * - other users see only administrated ones
	 *
	 * @return Response
	 */
	public function index() {
		return View::make('settings/association/index')->with('associations', AssociationHandler::getUsersAssociations(Auth::user()));
	}

	/**
	 * Show the form for creating a new association
	 *
	 * @return Response
	 */
	public function create() {
		$this->authorize('create-association');

		return View::make('settings/association/form')
				->withUsers($this->selectNone + User::all()->lists('username', 'username')->all());
	}

	/**
	 * Validate and store new association
	 * 
	 * @return Response
	 */
	public function store() {
		$this->authorize('create-association');

		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			AssociationHandler::create($data);
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
	 * @return Responce
	 */
	public function show(Association $association) {
		$this->authorize('show-association', $association);

		return View::make('settings/association/show')->with('data', $association);
	}

	/**
	 * Show the form for editing the specified association
	 *
	 * redirect if user is neither maintainer nor admin
	 * 
	 * @param  Association $association
	 * @return Response
	 */
	public function edit(Association $association) {
		$this->authorize('create-association', $association);

		return View::make('settings/association/form')
				->withData($association)
				->withUsers(Auth::user()->admin ? $this->selectNone + User::all()->lists('username', 'username')->all() : Auth::user()->lists('username', 'username')->all());
	}

	/**
	 * Update the specified association in storage
	 *
	 * @param Association $association       	
	 * @return Response
	 */
	public function update(Association $association) {
		$this->authorize('create-association', $association);

		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			AssociationHandler::update($association, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.associations/edit', ['association' => $association->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.associations');
	}

}
