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
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use TastingNumberHandler;
use Weinstein\Exception\ValidationException;
use App\Http\Controllers\BaseController;
use App\Competition\Competition;
use App\Competition\CompetitionState;
use App\Competition\Tasting\TastingNumber;

class TastingNumberController extends BaseController {

	/**
	 * Filter user administrates competition
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterCompetitionAdmin($route, $request) {
		$tastingNumber = Route::input('tastingnumber');
		$competition = is_null($tastingNumber) ? Route::input('competition') : $tastingNumber->wine->competition;

		if (!$competition->administrates(Auth::user())) {
			$this->abortNoAccess($route, $request);
		}
	}

	public function filterCompetitionState($route, $request) {
		$tastingNumber = Route::input('tastingnumber');
		$competition = is_null($tastingNumber) ? Route::input('competition') : $tastingNumber->wine->competition;

		if (!in_array($competition->competitionstate->id, [
			    CompetitionState::STATE_ENROLLMENT,
			    CompetitionState::STATE_TASTINGNUMBERS1,
			    CompetitionState::STATE_TASTINGNUMBERS2
			])) {
			$this->abortNoAccess($route, $request);
		}
	}

	public function filterEnrollmentFinished($route, $request) {
		$competition = Route::input('competition');
		if (!$competition->enrollmentFinished()) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterTranslate($route, $request) {
		$competition = Route::input('competition');

		if (!in_array($competition->competitionstate->id, [CompetitionState::STATE_TASTING1, CompetitionState::STATE_TASTING2])) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->middleware('auth');
		$this->middleware('@filterCompetitionAdmin', [
		    'except' => [
			'translate',
		    ],
		]);
		$this->middleware('@filterCompetitionState');
		$this->middleware('@filterTranslate', [
		    'only' => [
			'translate',
		    ],
		]);
		$this->middleware('@filterEnrollmentFinished');
	}

	/**
	 * Show list of all assigned tasting numbers
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function index(Competition $competition) {
		$showAdd = false;
		;
		$showComplete = false;
		$left = -1;
		if ($competition->competitionstate->id === CompetitionState::STATE_ENROLLMENT) {
			$showAdd = true;
		} else if ($competition->competitionstate->id === CompetitionState::STATE_TASTINGNUMBERS1) {
			$left = $competition->wines()->count() - $competition->wines()->withTastingNumber($competition->getTastingStage())->count();
			$showComplete = $left === 0;
			$showAdd = !$showComplete; //show add if not all wines are assigned
		} else if ($competition->competitionstate->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			// there is no check (for now)
			// kdb wines do not have to be tasted a second time
			$kdbWines = $competition->wines()->kdb()->count();
			$tastingNumber2 = $competition->wines()->withTastingNumber($competition->getTastingStage())->count();
			$showAdd = $kdbWines !== $tastingNumber2; //show add as long as not all wines are assigned
			$showComplete = true;
		}

		return View::make('competition/tasting/tasting-number/index')
				->withNumbers(TastingNumberHandler::getAll($competition, $competition->getTastingStage()))
				->withShowAdd($showAdd)
				->withLeft($left)
				->withFinished($showComplete);
	}

	/**
	 * Assign new tasting number
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function assign(Competition $competition) {
		return View::make('competition/tasting/tasting-number/form');
	}

	/**
	 * Validate and store newly assigned tasting number
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function store(Competition $competition) {
		try {
			TastingNumberHandler::create(Input::all(), $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.numbers/assign', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		//if all wines are assigned, redirect to the list index page
		if (in_array($competition->competitionstate->id, [CompetitionState::STATE_ENROLLMENT, CompetitionState::STATE_TASTINGNUMBERS1])) {
			$allWines = $competition->wines()->count();
			$assigned = $competition->wines()->withTastingNumber($competition->getTastingStage())->count();
		} elseif ($competition->competitionstate->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			$allWines = $competition->wines()->kdb()->count();
			$assigned = $competition->wines()->kdb()->withTastingNumber($competition->getTastingStage())->count();
		}
		if ($assigned === $allWines) {
			return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
		}
		return Redirect::route('tasting.numbers/assign', ['competition' => $competition->id]);
	}

	/**
	 * Ask user about deallocating the specified tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @return Response
	 */
	public function deallocate(TastingNumber $tastingNumber) {
		return View::make('competition/tasting/tasting-number/deallocate')->with('data', $tastingNumber);
	}

	/**
	 * Check users choice and eventually delete specified tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @return Response
	 */
	public function delete(TastingNumber $tastingNumber) {
		if (Input::get('del') == 'Ja') {
			TastingNumberHandler::delete($tastingNumber);
		}
		return Redirect::route('tasting.numbers', ['competition' => $tastingNumber->wine->competition->id]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function import(Competition $competition) {
		return View::make('competition/tasting/tasting-number/import');
	}

	/**
	 * Validate and store import files tasting numbers
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function importStore(Competition $competition) {
		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
			}
			$rowsImported = TastingNumberHandler::import($file, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.numbers/import', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		Session::flash('rowsImported', $rowsImported);
		return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @param wine nr $id
	 * @return Response
	 */
	public function translate(Competition $competition, $id) {
		$tastingNumber = $competition
			->tastingnumbers()
			->where('tastingnumber.nr', '=', $id)
			->where('tastingstage_id', '=', $competition->getTastingStage()->id)
			->select('tastingnumber.id')
			->first();

		if ($tastingNumber) {
			return Response::json([
				    'tnr' => $tastingNumber->id,
			]);
		}
		return Response::json([
			    'error' => 'Kostnummer konnte nicht geladen werden',
		]);
	}

}
