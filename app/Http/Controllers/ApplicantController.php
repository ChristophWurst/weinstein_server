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
use App\MasterData\Applicant;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use function view;

class ApplicantController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $viewFactory;

	/**
	 * @param MasterDataStore $masterDataStore
	 * @param AuthManager $auth
	 * @param Factory $viewFactory
	 */
	public function __construct(MasterDataStore $masterDataStore, AuthManager $auth, Factory $viewFactory) {
		$this->masterDataStore = $masterDataStore;
		$this->auth = $auth;
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Display a listing of all applicants the user is permitted to see
	 *
	 * @return Response
	 */
	public function index() {
		$user = $this->auth->user();
		$applicants = $this->masterDataStore->getApplicants($user);
		return $this->viewFactory->make('settings/applicant/index', [
			'applicants' => $applicants,
		]);
	}

	/**
	 * Show the form for creating a new applicant
	 *
	 * @return Response
	 */
	public function create() {
		$this->authorize('create-applicant');

		return $this->viewFactory->make('settings/applicant/form', [
			'associations' => $this->masterDataStore->getAssociations()->lists('select_label', 'id')->all(),
			'users' => $this->selectNone + $this->masterDataStore->getUsers()->lists('username', 'username')->all(),
		]);
	}

	/**
	 * Store a newly created applicant in storage
	 * 
	 * @return Response
	 */
	public function store() {
		$this->authorize('create-applicant');

		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			$this->masterDataStore->createApplicant($data);
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
		$this->authorize('show-applicant', $applicant);

		return $this->viewFactory->make('settings/applicant/show', [
				'data' => $applicant
		]);
	}

	/**
	 * Show the form for importing applicants
	 *
	 * @return Response
	 */
	public function getImport() {
		$this->authorize('import-applicant');

		return $this->viewFactory->make('settings/applicant/import');
	}

	/**
	 * Read uploaded .csv file, parse, validate and save its content
	 *
	 * @return Response
	 */
	public function postImport() {
		$this->authorize('import-applicant');

		//check for file existense
		if (!Input::hasFile('xlsfile')) {
			return Redirect::route('settings.applicants/import');
		}

		try {
			$file = Input::file('xlsfile');
			$rowsImported = $this->masterDataStore->importApplicants($file);
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
		$this->authorize('edit-applicant', $applicant);

		$editId = $applicant->association->administrates($this->auth->user());
		return $this->viewFactory->make('settings/applicant/form', [
			'applicant' => $applicant,
			'editId' => $editId,
			'associations' => $this->masterDataStore->getAssociations()->lists('select_label', 'id')->all(),
			'users' => $this->selectNone + $this->masterDataStore->getUsers()->lists('username', 'username')->all(),
		]);
	}

	/**
	 * Update the specified applicant in storage
	 *
	 * @param Applicant $applicant        	
	 * @return Response
	 */
	public function update(Applicant $applicant) {
		$this->authorize('edit-applicant', $applicant);

		$data = Input::all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}

		// Ignore id if user isn't the association admin
		if (!$applicant->association->administrates($this->auth->user())) {
			$data['id'] = $applicant->id;
		}
		if (!$this->auth->user()->isAdmin()) {
			unset($data['wuser_username']);
		} else if (isset($data['wuser_username']) && $data['wuser_username'] === '') {
			$data['wuser_username'] = null;
		}


		try {
			$this->masterDataStore->updateApplicant($applicant, $data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.applicants/edit', ['applicant' => $applicant->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('settings.applicants');
	}

}
