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

use App\Contracts\WineHandler;
use App\Exceptions\ValidationException;
use App\FlawExport;
use App\Http\Controllers\BaseController;
use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\WineSort;
use App\Wine;
use App\Wine\EnrollmentForm;
use App\WineExport;
use App\WineQuality;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response as Resp;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class WineController extends BaseController {

	/** @var Factory */
	private $viewFactory;

	/** @var WineHandler */
	private $wineHandler;

	/**
	 * @param WineHandler $wineHandler
	 * @param Factory $viewFactory
	 */
	public function __construct(WineHandler $wineHandler, Factory $viewFactory) {
		$this->wineHandler = $wineHandler;
		$this->viewFactory = $viewFactory;
	}

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
	 * @return View
	 */
	public function index(Competition $competition) {
		$competitionAdmin = $competition->administrates(Auth::user());

		return $this->viewFactory->make('competition/wines/index', [
			'competition' => $competition,
			'user' => Auth::user(),
			'competition_admin' => $competitionAdmin,
			'wine_url' => route('wines.index'),
			'show_add_wine' => $competition->competitionState->id === CompetitionState::STATE_ENROLLMENT,
			'show_edit_wine' => $competition->competitionState->id === CompetitionState::STATE_ENROLLMENT,
			'show_rating1' => $competition->competitionState->id >= CompetitionState::STATE_TASTING1,
			'show_rating2' => $competitionAdmin && $competition->competitionState->id >= CompetitionState::STATE_TASTING2,
			'edit_kdb' => $competition->competitionState->id === CompetitionState::STATE_KDB,
			'show_kdb' => $competition->competitionState->id >= CompetitionState::STATE_KDB,
			'show_complete_kdb' => $competition->competitionState->id === CompetitionState::STATE_KDB,
			'edit_excluded' => $competition->competitionState->id === CompetitionState::STATE_EXCLUDE,
			'show_excluded' => $competition->competitionState->id >= CompetitionState::STATE_EXCLUDE,
			'show_complete_exclude' => $competition->competitionState->id === CompetitionState::STATE_EXCLUDE,
			'edit_sosi' => $competition->competitionState->id === CompetitionState::STATE_SOSI,
			'show_sosi' => $competition->competitionState->id >= CompetitionState::STATE_SOSI,
			'show_complete_sosi' => $competition->competitionState->id === CompetitionState::STATE_SOSI,
			'show_edit_chosen' => $competition->competitionState->id === CompetitionState::STATE_CHOOSE,
			'show_chosen' => $competition->competitionState->id >= CompetitionState::STATE_CHOOSE,
			'edit_chosen' => $competition->competitionState->id === CompetitionState::STATE_CHOOSE,
			'show_complete_choosing' => $competition->competitionState->id === CompetitionState::STATE_CHOOSE,
			'export_flaws' => $competition->competitionState->id >= CompetitionState::STATE_KDB,
			'show_enrollment_pdf_export'=> $competition->competitionState->is(CompetitionState::STATE_ENROLLMENT),
		]);
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

		return $this->viewFactory->make('competition/wines/show', [
			'wine' => $wine,
			'show_edit_wine' => $showEdit,
			'show_rating2' => $competitionAdmin,
		]);
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
	 * @param Request $request
	 * @return View
	 */
	public function create(Competition $competition, Request $request) {
		$user = Auth::user();
		$applicants = $competition->administrates($user) ? Applicant::all() : $user->applicants;
		return $this->viewFactory->make('competition/wines/form', [
			'competition' => $competition,
			'competition_admin' => $competition->administrates($user),
			'id' => Wine::maxId($competition) + 1,
			'applicants' => $applicants->lists('select_label', 'id')->all(),
			'associations' => ['auto' => 'automatisch zuordnen'] + Association::all()->lists('select_label', 'id')->all(),
			'winesorts' => WineSort::all()->lists('select_label', 'id')->all(),
			'winequalities' => ['none' => '0 - keine'] + WineQuality::get()->lists('select_label', 'id')->all(),
			'show_nr' => $competition->administrates(Auth::user()),
			'success' => $request->session()->has('wine_added_successfully'),
		]);
	}

	/**
	 * Store the newly created wine
	 * 
	 * @param Competition $competition
	 * @param Request $request
	 * @return Resp
	 */
	public function store(Competition $competition, Request $request) {
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

			$this->wineHandler->create($data, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/create', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		$request->flashOnly([
			'applicant_id',
		]);
		$request->session()->flash('wine_added_successfully', true);
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
	 * @return View
	 */
	public function edit(Wine $wine) {
		$this->authorize('update-wine', $wine);

		$user = Auth::user();
		$applicants = $wine->competition->administrates($user) ? Applicant::all() : $user->applicants;
		return $this->viewFactory->make('competition/wines/form', [
			'success' => false,
			'wine' => $wine,
			'applicants' => $applicants->lists('select_label', 'id')->all(),
			'associations' => ['auto' => 'automatisch zuordnen'] + Association::all()->lists('select_label', 'id')->all(),
			'winesorts' => WineSort::all()->lists('select_label', 'id')->all(),
			'winequalities' => ['none' => '0 - keine'] + WineQuality::get()->lists('select_label', 'id')->all(),
			'show_nr' => $wine->competition->administrates(Auth::user()),
		]);
	}

	/**
	 * Store the changed data
	 * 
	 * TODO: check if related data exists -> no more changes to applicant,
	 * association, competition, sort
	 * 
	 * @param Wine $wine
	 * @return Resp
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

			$this->wineHandler->update($wine, $data);
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
	 * @return Resp
	 */
	public function delete(Wine $wine) {
		$this->authorize('delete-wine', $wine);

		return $this->viewFactory->make('competition/wines/delete')->withWine($wine);
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
			$this->wineHandler->delete($wine);
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
			$this->wineHandler->updateKdb($wine, Input::only('value'));
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
	 * @return View
	 */
	public function importKdb(Competition $competition) {
		$this->authorize('import-kdb-wines', $competition);

		return $this->viewFactory->make('competition/wines/import-kdb');
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
			$rowsImported = $this->wineHandler->importKdb($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-kdb', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function importExcluded(Competition $competition) {
		$this->authorize('import-excluded-wines', $competition);

		return $this->viewFactory->make('competition/wines/import-excluded');
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
			$rowsImported = $this->wineHandler->importExcluded($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('enrollment.wines/import-exclude', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('enrollment.wines', ['competition' => $competition->id]);
	}

	/**
	public function updateSosi(Wine $wine) {
		$this->authorize('update-wine', $wine);

		if (!$wine->kdb) {
			return Response::json([
					'error' => 'Fehler: Dieser Wein ist nicht im KdB',
					'wines' => Wine::sosi()->lists('id')->all(),
			]);
		}
		try {
			$this->wineHandler->updateSosi($wine, Input::only('value'));
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
	 * @return View
	 */
	public function importSosi(Competition $competition) {
		$this->authorize('import-sosi-wines', $competition);

		return $this->viewFactory->make('competition/wines/import-sosi');
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
			$rowsImported = $this->wineHandler->importSosi($file, $competition);
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
			$this->wineHandler->updateChosen($wine, Input::only('value'));
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
	 * Export competitions wines as Excel
	 * 
	 * @param Competition $competition
	 * @return Resp
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
	 * @return Resp
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
	 * @return Resp
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
	 * @return Resp
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
