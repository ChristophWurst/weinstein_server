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
use Illuminate\Support\Facades\View;
use Weinstein\Support\Facades\TastingSessionHandlerFacade as TastingSessionHandler;
use Weinstein\Exception\ValidationException;
use Weinstein\Competition\TastingSession\TastingProtocol as TastingProtocol;
use App\Http\Controllers\BaseController;
use App\Competition\Competition;
use App\Competition\CompetitionState;
use App\Competition\Tasting\Commission;
use App\Competition\Tasting\TastingSession;
use App\Competition\Tasting\TastingStage;
use App\User;

class TastingSessionController extends BaseController {

	/**
	 * Filter user administrates tasting session
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterTastingSessionAdmin($route, $request) {
		$tastingSession = Route::input('tastingsession');

		if (!$tastingSession->administrates(Auth::user())) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterTastingStage($route, $request) {
		$competition = Route::input('competition');
		if (is_null($competition)) {
			$ts = Route::input('tastingsession');
			$competition = $ts->competition;
		}

		if (!in_array($competition->competitionstate->id, [CompetitionState::STATE_TASTING1, CompetitionState::STATE_TASTING2])) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Filter session is not locked
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterTastingSessionLocked($route, $request) {
		$ts = Route::input('tastingsession');

		if ($ts->locked) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterTastingSessionDeletable($route, $request) {
		$ts = Route::input('tastingsession');

		if (($ts->tasters()->count() > 0)) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->middleware('auth');
		$this->middleware('@filterTastingSessionAdmin', [
		    'except' => [
			'index',
			'add',
			'store',
			'exportResult',
			'statistic',
		    ],
		]);
		$this->middleware('@filterTastingStage', [
		    'except' => [
			'exportProtocol',
		    ],
		]);
		$this->middleware('@filterTastingSessionLocked', [
		    'except' => [
			'index',
			'add',
			'store',
			'show',
			'tasters',
			'exportProtocol',
		    ],
		]);
		$this->middleware('@filterTastingSessionDeletable', [
		    'only' => [
			'delete',
			'destroy',
		    ],
		]);
	}

	/**
	 * Share common view data
	 * 
	 * @param Competition $competition
	 */
	private function shareCommonViewData(Competition $competition) {
		View::share('competition', $competition);
		View::share('tastingstage', $competition->getTastingStage());
		View::share('tastingsessions', TastingSessionHandler::getAll($competition, $competition->getTastingStage(), Auth::user()));
		$tasting1 = $competition->competitionstate->id === CompetitionState::STATE_TASTING1;
		$tasting2 = $competition->competitionstate->id === CompetitionState::STATE_TASTING2;
		View::share('show_finish1', $tasting1 && $competition->wine_details()->count() === $competition->wine_details()->whereNotNull('rating1')->count());
		View::share('show_finish2', $tasting2 && $competition->wines()->withTastingNumber(TastingStage::find(2))->count() === $competition->wine_details()->kdb()->whereNotNull('rating2')->count());
	}

	/**
	 * list current competitions tasting sessions
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function index(Competition $competition) {
		$this->shareCommonViewData($competition);
		return View::make('competition/tasting/tasting-session/index');
	}

	/**
	 * Show form for adding new session
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function add(Competition $competition) {
		$this->shareCommonViewData($competition);
		return View::make('competition/tasting/tasting-session/form')
				->withUsers($this->selectNone + User::all()->lists('username', 'username')->all());
	}

	/**
	 * Validate and store newly created sessions
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function store(Competition $competition) {
		$data = Input::all();
		//unset user if set to 'none'
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			$tastingSession = TastingSessionHandler::create($data, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.sessions/add', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
	}

	/**
	 * Show the specified tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function show(TastingSession $tastingSession) {
		$this->shareCommonViewData($tastingSession->competition);
		return View::make('competition/tasting/tasting-session/show')
				->withData($tastingSession)
				->withTastingFinished(TastingSessionHandler::tastingFinished($tastingSession->competition, $tastingSession->competition->tastingstage));
	}

	/**
	 * Export session results as Excel
	 * 
	 * @param TastingSession $tastingSession
	 * @param Commission $commission
	 * @return type
	 */
	public function exportResult(TastingSession $tastingSession, Commission $commission) {
		$wines = $tastingSession
			->tastedwines()
			->where('commission_id', '=', $commission->id)
			->orderBy('tastingnumber_nr')
			->get();
		$we = new ResultExport($wines);
		$filename = 'Kostsitzungsauswertung '
			. $tastingSession->tastingstage->id
			. '-'
			. $tastingSession->nr
			. $commission->side
			. '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($we->asExcel(), $filename, $headers);
	}

	/**
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function exportProtocol(TastingSession $tastingSession) {
		$tp = new TastingProtocol($tastingSession);
		$filename = 'Kostprotokoll ' . $tastingSession->tastingstage->id . '-' . $tastingSession->nr . '.xls';
		$headers = [
		    'Content-Type' => 'application/vnd.ms-excel',
		    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
		];
		return Response::download($tp->asExcel(), $filename, $headers);
	}

	/**
	 * Show update form
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function edit(TastingSession $tastingSession) {
		$this->shareCommonViewData($tastingSession->competition);
		return View::make('competition/tasting/tasting-session/form')
				->withData($tastingSession)
				->withUsers($this->selectNone + User::all()->lists('username', 'username')->all());
	}

	/**
	 * Validate and store updated values
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function update(TastingSession $tastingSession) {
		$data = Input::all();
		//unset user if set to 'none'
		if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
			unset($data['wuser_username']);
		}
		try {
			TastingSessionHandler::update($tastingSession, $data, $tastingSession->competition);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.sessions/edit', ['tastingsession' => $tastingSession->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
	}

	/**
	 * 
	 * @param TastingSession $tastingSession
	 * @param Commission $commission
	 * @return json
	 */
	public function tasters(TastingSession $tastingSession, Commission $commission) {
		return Response::json($commission->tasters->toArray());
	}

	/**
	 * Add a new taster
	 * 
	 * @param TastingSession $tastingSession
	 */
	public function addTaster(TastingSession $tastingSession) {
		$data = Input::all();
		try {
			TastingSessionHandler::addTaster($data, $tastingSession);
		} catch (ValidationException $ve) {
			return Response::json([
				    'valid' => false,
				    'errors' => $ve->getErrors()->getMessages(),
			]);
		}
		$commission = Commission::find($data['commission_id']);
		$tasters = $commission->tasters()->orderBy('nr')->get()->toArray();
		return Response::json([
			    'valid' => true,
			    'tasters' => $tasters,
		]);
	}

	public function statistics(TastingSession $tastingSession) {
		$this->shareCommonViewData($tastingSession->competition);
		return View::make('competition/tasting/tasting-session/statistics')
				->withTastingSession($tastingSession);
	}

	/**
	 * Show user confirmation for completing/locking tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function complete(TastingSession $tastingSession) {
		$this->shareCommonViewData($tastingSession->competition);
		return View::make('competition/tasting/tasting-session/complete')->with([
			    'data' => $tastingSession,
		]);
	}

	/**
	 * Lock tastingstation
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function lock(TastingSession $tastingSession) {
		if (Input::has('del') && Input::get('del') == 'Ja') {
			TastingSessionHandler::lock($tastingSession);
		}
		return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
	}

	/**
	 * Show user confirmation for deleting tasting sessions
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function delete(TastingSession $tastingSession) {
		$this->shareCommonViewData($tastingSession->competition);
		return View::make('competition/tasting/tasting-session/delete')->with([
			    'data' => $tastingSession,
		]);
	}

	/**
	 * Destroy database entry
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function destroy(TastingSession $tastingSession) {
		$competition = $tastingSession->competition;
		if (Input::get('del') == 'Ja') {
			TastingSessionHandler::delete($tastingSession);
		}
		return Redirect::route('tasting.sessions', ['competition' => $competition->id]);
	}

}
