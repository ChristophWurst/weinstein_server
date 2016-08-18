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
	 * List all wines
	 * 
	 * admin sees all
	 * others see their administrated associations/applicants wines
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function index(Competition $competition) {
		$this->authorize('list-wines', $competition);

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
		$this->authorize('show-wine', $wine);

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
		$this->authorize('redirect-wine', $competition);

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
		$this->authorize('kdb-wines', $competition);

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
		$this->authorize('excluded-wines', $competition);

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
		$this->authorize('sosi-wines', $competition);

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
		$this->authorize('chosen-wines', $competition);

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
		$this->authorize('create-wine', $competition);

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
		$this->authorize('create-wine', $competition);

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
		$this->authorize('print-wine-enrollment-pdf', $wine);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('delete-wine', $wine);

		return View::make('competition/wines/delete')->withWine($wine);
	}

	/**
	 * Delete the wine
	 * 
	 * @param Wine $wine
	 * @return type
	 */
	public function destroy(Wine $wine) {
		$this->authorize('delete-wine', $wine);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('import-kdb-wines', $competition);

		return View::make('competition/wines/import-kdb');
	}

	/**
	 * Validate and store import files kdb wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importKdbStore(Competition $competition) {
		$this->authorize('import-kdb-wines', $competition);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('import-excluded-wines', $competition);

		return View::make('competition/wines/import-excluded');
	}

	/**
	 * Validate and store import files exclude wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importExcludedStore(Competition $competition) {
		$this->authorize('import-excluded-wines', $competition);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('import-sosi-wines', $competition);

		return View::make('competition/wines/import-sosi');
	}

	/**
	 * Validate and store import files sosi wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importSosiStore(Competition $competition) {
		$this->authorize('import-sosi-wines', $competition);

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
		$this->authorize('update-wine', $wine);

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
		$this->authorize('import-chosen-wines', $competition);

		return View::make('competition/wines/import-chosen');
	}

	/**
	 * Validate and store import files chosen wines
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importChosenStore(Competition $competition) {
		$this->authorize('import-chosen-wines', $competition);

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
		$this->authorize('export-wines', $competition);

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
		$this->authorize('export-wines-kdb', $competition);

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
		$this->authorize('export-wines-sosi', $competition);

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
		$this->authorize('export-wines-chosen', $competition);

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
		$this->authorize('export-wines-flaws', $competition);

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
