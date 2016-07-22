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

namespace App\Http\Controllers\Competition\Wine;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use WineHandler;
use App\Http\Controllers\BaseController;
use App\Applicant;
use App\Association;
use App\Competition\Competition;
use App\Competition\CompetitionState;
use App\Competition\Wine\FlawExport;
use App\Competition\Wine\Wine;
use App\Competition\Wine\WineExport;
use App\WineSort;
use App\Competition\EnrollmentForm;
use App\Competition\Wine\WineQuality;
use Weinstein\Exception\ValidationException;

class WineController extends BaseController {

	/**
	 * Convert german decimal point to international format
	 * 
	 * e.g. 3,4 -> 3.4
	 * 
	 * @param string|int $val
	 * @return string
	 */
	private function commaToDot($val) {
		return str_replace(",", ".", $val);
	}

	/**
	 * Filter user administrates association order
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompetitionAdmin($route, $request) {
		$wine = Route::input('wine');
		$competition = is_null($wine) ? Route::input('competition') : $wine->competition;

		if (!$competition->administrates(Auth::user())) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Allow wine adding only when competition state === ENROLLMENT
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterAllowAdding($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::where('description', '=', 'ENROLLMENT')->first()->id) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterAllowEditing($route, $request) {
		$wine = Route::input('wine');
		$competition = $wine->competition;
		$user = Auth::user();

		if (!$user->admin && !is_null($wine->nr)) {
			// Once ID is set, only admin may edit the wine
			$this->abortNoAccess($route, $request);
		}

		if ($competition->competitionstate->id !== CompetitionState::where('description', '=', 'ENROLLMENT')->first()->id) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterKdb($route, $request) {
		$competition = Route::input('wine')->competition;

		if ($competition->competitionstate->id !== CompetitionState::STATE_KDB) {
			return Response::json([
				    'error' => 'KdB darf nicht (mehr) verändert werden!'
			]);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterKdbImport($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_KDB) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterExcluded($route, $request) {
		$competition = Route::input('wine')->competition;

		if ($competition->competitionstate->id !== CompetitionState::STATE_EXCLUDE) {
			return Response::json([
				    'error' => 'KdB darf nicht (mehr) verändert werden!'
			]);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterExcludedImport($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_EXCLUDE) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterSosi($route, $request) {
		$competition = Route::input('wine')->competition;

		if ($competition->competitionstate->id !== CompetitionState::STATE_SOSI) {
			return Response::json([
				    'error' => 'SoSi darf nicht (mehr) verändert werden!'
			]);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterSosiImport($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_SOSI) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterChosen($route, $request) {
		$wine = Route::input('wine');
		$competition = $wine->competition;

		if (!$wine->applicant->association->administrates(Auth::user()) || $competition->competitionstate->id !== CompetitionState::STATE_CHOOSE) {
			// Only association admin is allowed to change the value
			return Response::json([
				    'error' => 'Auswahl darf nicht (mehr) verändert werden!'
			]);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 * @return type
	 */
	public function filterChosenImport($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_CHOOSE) {
			$this->abortNoAccess($route, $request);
		}
	}

	public function filterExportFlaws($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id < CompetitionState::STATE_KDB) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterWineAdmin($route, $request) {
		$wine = Route::input('wine');

		if (!$wine->administrates(Auth::user())) {
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
		$this->beforeFilter('@filterCompetitionAdmin', [
		    'except' => [
			'index',
			'create',
			'store',
			'show',
			'redirect',
			'edit',
			'update',
			'delete',
			'destroy',
			'chosen',
			'updateChosen',
			'exportFlaws',
		    ],
		]);
		$this->beforeFilter('@filterAllowAdding', [
		    'only' => [
			'create',
			'store',
		    ],
		]);
		$this->beforeFilter('@filterAllowEditing', [
		    'only' => [
			'edit',
			'update',
			'delete',
			'destroy',
		    ],
		]);
		$this->beforeFilter('@filterKdb', [
		    'only' => [
			'updateKdb',
		    ],
		]);
		$this->beforeFilter('@filterKdbImport', [
		    'only' => [
			'importKdb',
			'importKdbStore',
		    ],
		]);
		$this->beforeFilter('@filterExcluded', [
		    'only' => [
			'updateExcluded',
		    ],
		]);
		$this->beforeFilter('@filterExcludedImport', [
		    'only' => [
			'importExcluded',
			'importExcludedStore',
		    ],
		]);
		$this->beforeFilter('@filterSosi', [
		    'only' => [
			'updateSosi',
		    ],
		]);
		$this->beforeFilter('@filterSosiImport', [
		    'only' => [
			'importSosi',
			'importSosiStore',
		    ],
		]);
		$this->beforeFilter('@filterChosen', [
		    'only' => [
			'updateChosen',
		    ],
		]);
		$this->beforeFilter('@filterChosenImport', [
		    'only' => [
			'importChosen',
			'importChosenStore',
		    ],
		]);
		$this->beforeFilter('@filterWineAdmin', [
		    'only' => [
			'show',
			'edit',
			'update',
			'delete',
			'destroy',
		    ],
		]);
		$this->beforeFilter('@filterExportFlaws', [
		    'only' => [
			'exportFlaws',
		    ],
		]);
	}

	/**
	 * List all wines
	 * 
	 * admin sees all
	 * others see their administrated associations/applicants wines
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function index(Competition $competition) {
		$competitionAdmin = $competition->administrates(Auth::user());

		$wines = WineHandler::getUsersWines(Auth::user(), $competition, true)->orderBy('id')->paginate(50);

		return View::make('competition/wines/index')
				->withUser(Auth::user())
				->withCompetitionAdmin($competitionAdmin)
				->withWines($wines)
				->withShowAddWine($competition->competitionState->id === CompetitionState::STATE_ENROLLMENT)
				->withShowEditWine($competition->competitionState->id === CompetitionState::STATE_ENROLLMENT)
				->withShowRating1($competition->competitionState->id >= CompetitionState::STATE_TASTING1)
				->withShowRating2($competitionAdmin && $competition->competitionState->id >= CompetitionState::STATE_TASTING2)
				->withEditKdb($competition->competitionState->id === CompetitionState::STATE_KDB)
				->withShowKdb($competition->competitionState->id >= CompetitionState::STATE_KDB)
				->withShowCompleteKdb($competition->competitionState->id === CompetitionState::STATE_KDB)
				->withEditExcluded($competition->competitionState->id === CompetitionState::STATE_EXCLUDE)
				->withShowExcluded($competition->competitionState->id >= CompetitionState::STATE_EXCLUDE)
				->withShowCompleteExclude($competition->competitionState->id === CompetitionState::STATE_EXCLUDE)
				->withEditSosi($competition->competitionState->id === CompetitionState::STATE_SOSI)
				->withShowSosi($competition->competitionState->id >= CompetitionState::STATE_SOSI)
				->withShowCompleteSosi($competition->competitionState->id === CompetitionState::STATE_SOSI)
				->withEditChosen($competition->competitionState->id === CompetitionState::STATE_CHOOSE)
				->withShowChosen($competition->competitionState->id >= CompetitionState::STATE_CHOOSE)
				->withShowCompleteChoosing($competition->competitionState->id === CompetitionState::STATE_CHOOSE)
				->withExportFlaws($competition->competitionstate->id >= CompetitionState::STATE_KDB);
	}

	/**
	 * 
	 * @param Wine $wine
	 * @return View
	 */
	public function show(Wine $wine) {
		$competition = $wine->competition;
		$user = Auth::user();

		$competitionAdmin = $competition->administrates($user);

		$showEdit = $competition->competitionState->id === CompetitionState::STATE_ENROLLMENT;
		if (!$competitionAdmin && !is_null($wine->nr)) {
			// 'Normal' user is not allowed to edit wine once nr is set
			$showEdit = false;
		}

		return View::make('competition/wines/show')
				->withWine($wine)
				->withShowEditWine($showEdit)
				->withShowRating2($competitionAdmin);
	}

	public function redirect(Competition $competition, $nr) {
		$wine = $competition->wines()->where('nr', '=', $nr)->first();
		if (!$wine) {
			App::abort(404);
		}
		return Redirect::route('enrollment.wines/show', ['wine' => $wine->id]);
	}

	/**
	 * Get lists of all kdb wines
	 * 
	 * @param Competition $competition
	 */
	public function kdb(Competition $competition) {
		return Response::json([
			    'wines' => Wine::kdb()->lists('id')->all(),
		]);
	}

	/**
	 * Get lists of all excluded wines
	 * 
	 * @param Competition $competition
	 */
	public function excluded(Competition $competition) {
		return Response::json([
			    'wines' => Wine::excluded()->lists('id')->all(),
		]);
	}

	/**
	 * Get lists of all sosi wines
	 * 
	 * @param Competition $competition
	 */
	public function sosi(Competition $competition) {
		return Response::json([
			    'wines' => Wine::sosi()->lists('id')->all(),
		]);
	}

	/**
	 * Get lists of all chosen wines
	 * 
	 * @param Competition $competition
	 */
	public function chosen(Competition $competition) {
		return Response::json([
			    'wines' => Wine::chosen()->lists('id')->all(),
		]);
	}

	/**
	 * Create a new wine
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function create(Competition $competition) {
		$user = Auth::user();
		$applicants = $competition->administrates($user) ? Applicant::all() : $user->applicants;
		return View::make('competition/wines/form')
				->withId(Wine::maxId($competition) + 1)
				->withApplicants($applicants->lists('select_label', 'id')->all())
				->withAssociations(['auto' => 'automatisch zuordnen'] + Association::all()->lists('select_label', 'id')->all())
				->withWinesorts(WineSort::all()->lists('select_label', 'id')->all())
				->withWinequalities(['none' => '0 - keine'] + WineQuality::get()->lists('select_label', 'id')->all())
				->withShowNr($competition->administrates(Auth::user()));
	}

	/**
	 * Store the newly created wine
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function store(Competition $competition) {
		try {
			$data = Input::all();
			if (isset($data['alcohol'])) {
				$data['alcohol'] = $this->commaToDot($data['alcohol']);
			}
			if (isset($data['alcoholtot'])) {
				if (!empty($data['alcoholtot'])) {
					$data['alcoholtot'] = $this->commaToDot($data['alcoholtot']);
				} else {
					unset($data['alcoholtot']);
				}
			}
			if (isset($data['sugar'])) {
				$data['sugar'] = $this->commaToDot($data['sugar']);
			}
			if (isset($data['winequality_id']) && $data['winequality_id'] === 'none') {
				unset($data['winequality_id']);
			}

			$user = Auth::user();
			if (!$competition->administrates($user)) {
				//TODO: move to validation
				// Only admins may set the 'nr' attribute
				unset($data['nr']);

				if (isset($data['applicant_id'])) {
					// Make sure applicant IDs match
					$applicants = $user->applicants()->select('id')->get();
					if (!$applicants->contains('id', $data['applicant_id'])) {
						unset($data['applicant_id']);
					}
				}
			}

			WineHandler::create($data, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/create', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Input::flashOnly([
		    'applicant_id'
		]);
		return Redirect::route('enrollment.wines/create', ['competition' => $competition->id]);
	}

	public function enrollmentPdf(Wine $wine) {
		$form = new EnrollmentForm($wine);
		$path = $form->save();
		$filename = 'Wines';
		$headers = [
		    'Content-Type' => 'application/pdf',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($path, $filename, $headers);
	}

	/**
	 * Update an existing wine
	 * 
	 * @param Wine $wine
	 * @return Response
	 */
	public function edit(Wine $wine) {
		$user = Auth::user();
		$applicants = $wine->competition->administrates($user) ? Applicant::all() : $user->applicants;
		return View::make('competition/wines/form')
				->withWine($wine)
				->withApplicants($applicants->lists('select_label', 'id')->all())
				->withAssociations(['auto' => 'automatisch zuordnen'] + Association::all()->lists('select_label', 'id')->all())
				->withWinesorts(WineSort::all()->lists('select_label', 'id')->all())
				->withWinequalities(['none' => '0 - keine'] + WineQuality::get()->lists('select_label', 'id')->all())
				->withShowNr($wine->competition->administrates(Auth::user()));
	}

	/**
	 * Store the changed data
	 * 
	 * TODO: check if related data exists -> no more changes to applicant,
	 * association, competition, sort
	 * 
	 * @param Wine $wine
	 * @return Response
	 */
	public function update(Wine $wine) {
		try {
			$data = Input::all();
			if (isset($data['alcohol'])) {
				$data['alcohol'] = $this->commaToDot($data['alcohol']);
			}
			if (isset($data['alcoholtot']) && $data['alcoholtot'] !== '') {
				$data['alcoholtot'] = $this->commaToDot($data['alcoholtot']);
			} else {
				$data['alcoholtot'] = null;
			}
			if (isset($data['sugar'])) {
				$data['sugar'] = $this->commaToDot($data['sugar']);
			}
			if (isset($data['winequality_id']) && $data['winequality_id'] === 'none') {
				$data['winequality_id'] = null;
			}

			$user = Auth::user();
			if (!$wine->competition->administrates($user)) {
				// TODO: move to validation
				// Only admins may set the 'nr' attribute
				unset($data['nr']);

				if (isset($data['applicant_id'])) {
					// Make sure applicant IDs match
					$applicants = $user->applicants()->select('id')->get();
					if (!$applicants->contains('id', $data['applicant_id'])) {
						unset($data['applicant_id']);
					}
				}
			}

			WineHandler::update($wine, $data, $wine->competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/edit', ['wine' => $wine->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('enrollment.wines', ['competition' => $wine->competition->id]);
	}

	/**
	 * Show confirmation dialog for deleting the wine
	 * 
	 * @param Wine $wine
	 * @return View
	 */
	public function delete(Wine $wine) {
		return View::make('competition/wines/delete')->withWine($wine);
	}

	/**
	 * Delete the wine
	 * 
	 * @param Wine $wine
	 * @return type
	 */
	public function destroy(Wine $wine) {
		if (Input::get('del') == 'Ja') {
			WineHandler::delete($wine);
		}
		return Redirect::route('enrollment.wines', ['competition' => $wine->competition->id]);
	}

	/**
	 * 
	 * @param Wine $wine
	 */
	public function updateKdb(Wine $wine) {
		try {
			WineHandler::updateKdb($wine, Input::only('value'));
		} catch (ValidationException $ve) {
			return Response::json([
				    'error' => 'Fehler beim setzen von KdB',
				    'wines' => Wine::kdb()->lists('id')->all(),
			]);
		}
		return Response::json([
			    'wines' => Wine::kdb()->lists('id')->all(),
		]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function importKdb(Competition $competition) {
		return View::make('competition/wines/import-kdb');
	}

	/**
	 * Validate and store import files kdb wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importKdbStore(Competition $competition) {
		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
			}
			$rowsImported = WineHandler::importKdb($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-kdb', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	 * 
	 * @param Wine $wine
	 */
	public function updateExcluded(Wine $wine) {
		try {
			WineHandler::updateExcluded($wine, Input::only('value'));
		} catch (ValidationException $ve) {
			return Response::json([
				    'error' => 'Fehler beim setzen von Ex',
				    'wines' => Wine::excluded()->lists('id')->all(),
			]);
		}
		return Response::json([
			    'wines' => Wine::excluded()->lists('id')->all(),
		]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function importExcluded(Competition $competition) {
		return View::make('competition/wines/import-excluded');
	}

	/**
	 * Validate and store import files exclude wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importExcludedStore(Competition $competition) {
		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
			}
			$rowsImported = WineHandler::importExcluded($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-exclude', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	 * 
	 * @param Wine $wine
	 */
	public function updateSosi(Wine $wine) {
		if (!$wine->kdb) {
			return Response::json([
				    'error' => 'Fehler: Dieser Wein ist nicht im KdB',
				    'wines' => Wine::sosi()->lists('id')->all(),
			]);
		}
		try {
			WineHandler::updateSosi($wine, Input::only('value'));
		} catch (ValidationException $ve) {
			return Response::json([
				    'error' => 'Fehler beim setzen von SoSi',
				    'wines' => Wine::sosi()->lists('id')->all(),
			]);
		}
		return Response::json([
			    'wines' => Wine::sosi()->lists('id')->all(),
		]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function importSosi(Competition $competition) {
		return View::make('competition/wines/import-sosi');
	}

	/**
	 * Validate and store import files sosi wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importSosiStore(Competition $competition) {
		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
			}
			$rowsImported = WineHandler::importSosi($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-kdb', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	 * 
	 * @param Wine $wine
	 */
	public function updateChosen(Wine $wine) {
		try {
			WineHandler::updateChosen($wine, Input::only('value'));
		} catch (ValidationException $ve) {
			return Response::json([
				    'error' => 'Fehler beim setzen von SoSi',
				    'wines' => Wine::chosen()->lists('id')->all(),
			]);
		}
		return Response::json([
			    'wines' => Wine::chosen()->lists('id')->all(),
		]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function importChosen(Competition $competition) {
		return View::make('competition/wines/import-chosen');
	}

	/**
	 * Validate and store import files chosen wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importChosenStore(Competition $competition) {
		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
			}
			$rowsImported = WineHandler::importChosen($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-chosen', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	 * Export competitions wines as Excel
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function exportAll(Competition $competition) {
		$wines = $competition
			->wine_details()
			->orderBy('nr')
			->get();
		$we = new WineExport($wines);
		$filename = 'Weine ' . $competition->label . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($we->asExcel(), $filename, $headers);
	}

	/**
	 * Export competitions kdb wines as Excel
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function exportKdb(Competition $competition) {
		$wines = $competition
			->wine_details()
			->Kdb()
			->orderBy('nr')
			->get();
		$we = new WineExport($wines);
		$filename = 'Weine ' . $competition->label . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($we->asExcel(), $filename, $headers);
	}

	/**
	 * Export competitions sosi wines as Excel
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function exportSosi(Competition $competition) {
		$wines = $competition
			->wine_details()
			->Sosi()
			->orderBy('nr')
			->get();
		$we = new WineExport($wines);
		$filename = 'Weine ' . $competition->label . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($we->asExcel(), $filename, $headers);
	}

	/**
	 * Export competitions chosen wines as Excel
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function exportChosen(Competition $competition) {
		$wines = $competition
			->wine_details()
			->Chosen()
			->orderBy('nr')
			->get();
		$we = new WineExport($wines);
		$filename = 'Weine ' . $competition->label . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($we->asExcel(), $filename, $headers);
	}

	public function exportFlaws(Competition $competition) {
		$user = Auth::user();
		$wines = $competition
			->wine_details()
			->admin($user)
			->withFlaws()
			->Chosen()
			->orderBy('nr')
			->get();
		$export = new FlawExport($wines);
		$filename = 'Fehlerprotokoll ' . $competition->label . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($export->asExcel(), $filename, $headers);
	}

}
