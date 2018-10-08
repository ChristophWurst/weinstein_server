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
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

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
	 * @return View
	 */
	public function index() {
		/** @var User $user */
		$user = $this->auth->user();
		$applicants = $this->masterDataStore->getApplicants($user);
		return $this->viewFactory->make('settings/applicant/index', [
				'applicants' => $applicants,
				'canAdd' => $user->associations()->exists(),
		]);
	}

	/**
	 * Show the form for creating a new applicant
	 *
	 * @return View
	 */
	public function create() {
		$this->authorize('create-applicant');

		$associations = $this->masterDataStore->getAssociations($this->auth->user())->pluck('select_label', 'id')->all();
		$users = $this->selectNone + $this->masterDataStore->getUsers()->pluck('username', 'username')->all();

		return $this->viewFactory->make('settings/applicant/form', [
				'create' => true,
				'associations' => $associations,
				'users' => $users,
		]);
	}

	/**
	 * Store a newly created applicant in storage
	 * 
	 * @return Response
	 */
	public function store(Request $request) {
		$this->authorize('create-applicant');

		$data = $request->all();
		//remove default user of form's select
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			list ($applicant, $user, $password) = $this->masterDataStore->createApplicant($data);
		} catch (ValidationException $ve) {
			return Redirect::route('settings.applicants/create')
					->withErrors($ve->getErrors())
					->withInput();
		}
		$request->session()->flash('applicant_created', [$user->username, $password]);
		return Redirect::route('settings.applicants');
	}

	/**
	 * Display the specified applicant
	 *
	 * @param Applicant $applicant        	
	 * @return View
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
	 * @return View
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
	public function postImport(Request $request) {
		$this->authorize('import-applicant');

		//check for file existense
		if (!$request->hasFile('xlsfile')) {
			return Redirect::route('settings.applicants/import');
		}

		try {
			$file = $request->file('xlsfile');
			if (is_null($file) || !$file instanceof UploadedFile) {
				throw new ValidationException();
			}
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
	 * @return View
	 */
	public function edit(Applicant $applicant) {
		$this->authorize('edit-applicant', $applicant);

		$editId = $applicant->association->administrates($this->auth->user());
		$associations = $this->masterDataStore->getAssociations()->pluck('select_label', 'id')->all();
		$users = $this->selectNone + $this->masterDataStore->getUsers()->pluck('username', 'username')->all();
		return $this->viewFactory->make('settings/applicant/form',
				[
				'create' => false,
				'applicant' => $applicant,
				'editId' => $editId,
				'associations' => $associations,
				'users' => $users,
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
