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
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use CompetitionHandler;
use App\Http\Controllers\BaseController;
use App\Competition\Competition;
use App\Competition\CompetitionState;
use App\Competition\Tasting\TastingStage;

class CompetitionController extends BaseController {

	/**
	 * Filter user administrates competition
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterAdministrates($route, $request) {
		$competition = Route::input('competition');

		if (!$competition->administrates(Auth::user())) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteTastingNumbers($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id === CompetitionState::STATE_TASTINGNUMBERS1) {
			$withNumber = $competition->wines()->withTastingNumber(TastingStage::find(1))->count();
			$total = $competition->wine_details()->count();
			if ($withNumber < $total) {
				$this->abortNoAccess($route, $request);
			}
		} else if ($competition->competitionstate->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			// just allow it - there are no restrictions (for now)
		} else {
			Log::error('invalid competition state in complete-tastingnumbers-filter');
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteTasting($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id === CompetitionState::STATE_TASTING1) {
			$tasted = $competition->wine_details()->whereNotNull('rating1')->count();
			$total = $competition->wine_details()->count();
			if ($tasted < $total) {
				$this->abortNoAccess($route, $request);
			}
		} else if ($competition->competitionstate->id === CompetitionState::STATE_TASTING2) {
			// just allow it - there are no restrictions (for now)
		} else {
			Log::error('invalid competition state in complete-tasting-filter');
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteKdb($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_KDB) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteExcluded($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_EXCLUDE) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteSosi($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_SOSI) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompleteChoosing($route, $request) {
		$competition = Route::input('competition');

		if ($competition->competitionstate->id !== CompetitionState::STATE_CHOOSE) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->middleware('auth');
		$this->middleware('@filterAdmin', [
		    'only' => [
			'create',
			'store',
			'getReset',
			'postReset',
		    ],
		]);
		$this->middleware('@filterAdministrates', [
		    'only' => [
			'edit',
			'update',
			'completeTasting',
			'lockTasting',
			'completeKdb',
			'lockKdb',
			'completeSosi',
			'lockSosi',
			'completeChoosing',
			'lockChoosing',
		    ],
		]);
		$this->middleware('@filterCompleteTastingNumbers', [
		    'only' => [
			'completeTastingNumbers',
			'lockTastingNumbers',
		    ],
		]);
		$this->middleware('@filterCompleteTasting', [
		    'only' => [
			'completeTasting',
			'lockTasting',
		    ],
		]);
		$this->middleware('@filterCompleteKdb', [
		    'only' => [
			'completeKdb',
			'lockKdb',
		    ],
		]);
		$this->middleware('@filterCompleteExcluded', [
		    'only' => [
			'completeExcluded',
			'lockExcluded',
		    ],
		]);
		$this->middleware('@filterCompleteSosi', [
		    'only' => [
			'completeSosi',
			'lockSosi',
		    ],
		]);
		$this->middleware('@filterCompleteChoosing', [
		    'only' => [
			'completeChoosing',
			'lockChoosing',
		    ],
		]);
	}

	/**
	 * Show list of all competitions
	 * 
	 * @return Response
	 */
	public function index() {
		return View::make('settings/competition/index')->with([
			    'competitions' => CompetitionHandler::getAll(),
		]);
	}

	/**
	 * Show specified competitions
	 * 
	 * @param Competitoin $competition
	 * @return Response
	 */
	public function show(Competition $competition) {
		return View::make('competition/show')
				->withCompetition($competition)
				->withCompetitionStates(CompetitionState::all())
				->withWines($competition->wines()->count())
				->withWinesWithNr($competition->wines()->whereNotNull('nr')->count())
				->withWinesTasted1($competition->wine_details()->whereNotNull('rating1')->count())
				->withWinesTasted2($competition->wine_details()->kdb()->whereNotNull('rating2')->count())
				->withWinesKdb($competition->wines()->kdb()->count())
				->withWinesExcluded($competition->wines()->excluded()->count())
				->withWinesTastingNumber1($competition->wines()->withTastingNumber(TastingStage::find(1))->count())
				->withWinesTastingNumber2($competition->wines()->withTastingNumber(TastingStage::find(2))->count())
				->withWinesSosi($competition->wines()->sosi()->count())
				->withWinesChosen($competition->wines()->chosen()->count());
	}

	/**
	 * Show complete/lock confirmation page for specified tasting
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function completeTasting(Competition $competition, $tasting) {
		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}
		return View::make('competition/complete-tasting')
				->withData($competition)
				->withTasting($tasting);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function lockTasting(Competition $competition, $tasting) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockTasting($competition, $tasting);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeKdb(Competition $competition) {
		return View::make('competition/complete-kdb')
				->withData($competition);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockKdb(Competition $competition) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockKdb($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeExcluded(Competition $competition) {
		return View::make('competition/complete-excluded')
				->withData($competition);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockExcluded(Competition $competition) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockExcluded($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeSosi(Competition $competition) {
		return View::make('competition/complete-sosi')
				->withData($competition);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockSosi(Competition $competition) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockSosi($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeChoosing(Competition $competition) {
		return View::make('competition/complete-choosing')
				->withData($competition);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockChoosing(Competition $competition) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockChoosing($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * Show complete/lock confirmation page for specified tasting
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function completeTastingNumbers(Competition $competition, $tasting) {
		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}

		return View::make('competition/complete-tastingnumbers')->with([
			    'data' => $competition,
			    'tasting' => $tasting,
		]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function lockTastingNumbers(Competition $competition, $tasting) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockTastingNumbers($competition, $tasting);
			return Redirect::route('competition/show', ['competition' => $competition->id]);
		}
		return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function getReset(Competition $competition) {
		return View::make('settings/competition/reset');
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return type
	 */
	public function postReset(Competition $competition) {
		if (Input::has('reset') && Input::get('reset') == 'Ja') {
			CompetitionHandler::reset($competition);
		}
		return Redirect::route('settings.competitions');
	}

}
