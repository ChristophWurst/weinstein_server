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
use ApplicantHandler;
use App\Applicant;
use Association;
use Route;
use User;
use App\Http\Controllers\BaseController;
use Weinstein\Exception\ValidationException;

class ApplicantController extends BaseController {

	/**
	 * Filter user administrates applicant
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterAdministrates($route, $request) {
		$user = Auth::user();
		$applicant = Route::input('applicant');

		if (!$user->admin && !ApplicantHandler::isAdmin($user, $applicant)) {
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
			'getImport',
			'postImport',
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
	 * Display a listing of all applicants the user is permitted to see
	 *
	 * @return Response
	 */
	public function index() {
		return View::make('settings/applicant/index')
				->withApplicants(ApplicantHandler::getUsersApplicants(Auth::user()));
	}

	/**
	 * Show the form for creating a new applicant
	 *
	 * @return Response
	 */
	public function create() {
		return View::make('settings/applicant/form')
				->withAssociations(Association::all()->lists('select_label', 'id')->all())
				->withUsers($this->selectNone + User::all()->lists('username', 'username')->all());
	}

	/**
	 * Store a newly created applicant in storage
	 * 
	 * @return Response
	 */
	public function store() {
		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			ApplicantHandler::create($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.applicants/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.applicants');
	}

	/**
	 * Display the specified applicant
	 *
	 * @param Applicant $applicant        	
	 * @return Response
	 */
	public function show(Applicant $applicant) {
		return View::make('settings/applicant/show')->with([
			    'data' => $applicant
		]);
	}

	/**
	 * Show the form for importing applicants
	 *
	 * @return Response
	 */
	public function getImport() {
		return View::make('settings/applicant/import');
	}

	/**
	 * Read uploaded .csv file, parse, validate and save its content
	 *
	 * @return Response
	 */
	public function postImport() {
		//check for file existense
		if (!Input::hasFile('xlsfile')) {
			return Redirect::route('settings.applicants/import');
		}

		try {
			$rowsImported = ApplicantHandler::import(Input::file('xlsfile'));
		} catch (ValidationException $ve) {
			return Redirect::route('settings.applicants/import')->withErrors($ve->getErrors());
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('settings.applicants');
	}

	/**
	 * Show the form for editing the specified applicant
	 *
	 * @param Applicant $applicant        	
	 * @return Response
	 */
	public function edit(Applicant $applicant) {
		$editId = $applicant->association->administrates(Auth::user());
		return View::make('settings/applicant/form')
				->withApplicant($applicant)
				->withEditId($editId)
				->withAssociations(Association::all()->lists('select_label', 'id')->all())
				->withUsers($this->selectNone + User::all()->lists('username', 'username')->all());
	}

	/**
	 * Update the specified applicant in storage
	 *
	 * @param Applicant $applicant        	
	 * @return Response
	 */
	public function update(Applicant $applicant) {
		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}

		// Ignore id if user isn't the association admin
		if (!$applicant->association->administrates(Auth::user())) {
			$data['id'] = $applicant->id;
		}

		try {
			ApplicantHandler::update($applicant, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.applicants/edit', ['applicant' => $applicant->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.applicants');
	}

}
