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

namespace App\Http\Controllers\Competition\TastingSession\Tasting;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\BaseController;
use App\Competition\CompetitionState;
use App\Competition\Tasting\Commission;
use App\Competition\Tasting\TastingSession;
use App\Competition\Tasting\TastingNumber;
use Weinstein\Support\Facades\TastingHandlerFacade as TastingHandler;
use Weinstein\Exception\ValidationException;

class TastingController extends BaseController {

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
	 * Filter session is not locked
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterTastingSessionLocked($route, $request) {
		$tastingSession = Route::input('tastingsession');

		if ($tastingSession->locked) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCompetitionState($route, $request) {
		$tastingSession = Route::input('tastingsession');
		$competition = $tastingSession->competition;

		if (!in_array($competition->competitionstate->id, [CompetitionState::STATE_TASTING1, CompetitionState::STATE_TASTING2])) {
			Log::info('competition states do not match');
			$this->abortNoAccess($route, $request);
		}
		if ($tastingSession->tastingstage->id !== $competition->getTastingStage()->id) {
			Log::info('tasting stages do not match');
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterTastingNumber($route, $request) {
		$tastingSession = Route::input('tastingsession');
		$competition1 = $tastingSession->competition;
		$tastingNumber = Route::input('tastingnumber');
		$competition2 = $tastingNumber->wine->competition;

		//same competition?
		if ($competition1->id !== $competition2->id) {
			Log::info('competitions do not match');
			$this->abortNoAccess($route, $request);
		}

		//same tasting stage?
		$tastingStage1 = $tastingNumber->tastingstage;
		$tastingStage2 = $competition1->getTastingStage();

		if ($tastingStage1->id !== $tastingStage2->id) {
			Log::info('tasting stages do not match');
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * Make sure retastings are only done by commissions that belong to the 
	 * same competition as the tasting session does
	 * 
	 * @param type $route
	 * @param type $request
	 */
	public function filterCommissionMatches($route, $request) {
		$tastingSession = Route::input('tastingsession');
		$competition1 = $tastingSession->competition;
		$commission = Route::input('commission');
		$competition2 = $commission->tastingsession->competition;

		if ($competition1->id !== $competition2->id) {
			Log::info('commissions competition does not match');
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
		$this->beforeFilter('@filterTastingSessionAdmin', [
		    'except' => [
			'index',
		    ],
		]);
		$this->beforeFilter('@filterTastingSessionLocked');
		$this->beforeFilter('@filterCommissionMatches', [
		    'only' => [
			'edit',
			'update',
		    ],
		]);
		$this->beforeFilter('@filterCompetitionState');
		$this->beforeFilter('@filterTastingNumber', [
		    'only' => [
			'edit',
			'update',
		    ],
		]);
	}

	/**
	 * Add tasting results
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function add(TastingSession $tastingSession) {
		return View::make('competition/tasting/tasting-session/tasting/form')->with([
			    'competition' => $tastingSession->competition,
			    'tastingSession' => $tastingSession,
			    'tastingNumbers' => TastingHandler::getNextTastingNumbers($tastingSession),
		]);
	}

	/**
	 * Validate and store tasting results
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function store(TastingSession $tastingSession) {
		try {
			TastingHandler::create(Input::all(), $tastingSession);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.session/taste', ['tastingsession' => $tastingSession->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
	}

	/**
	 * Edit an existing tasting
	 * 
	 * @param TastingSession $tastingSession
	 * @param TastingNumber $tastingNumber
	 * @param Commission $commission
	 * @return Response
	 */
	public function edit(TastingSession $tastingSession, TastingNumber $tastingNumber, Commission $commission) {
		//check if tastingnumber has already been tasted
		if (!TastingHandler::isTasted($tastingNumber)) {
			Log::error('cannot retaste' . $tastingNumber->id . ', it has not yet been tasted');
			App::abort(500);
		}
		return View::make('competition/tasting/tasting-session/tasting/form')->with([
			    'edit' => true,
			    'competition' => $tastingSession->competition,
			    'commission' => $commission,
			    'tastingnumber' => $tastingNumber,
		]);
	}

	/**
	 * Update an existing tasting
	 * 
	 * @param TastingSession $tastingSession
	 * @param TastingNumber $tastingNumber
	 * @param Commission $commission
	 * @return type
	 */
	public function update(TastingSession $tastingSession, TastingNumber $tastingNumber, Commission $commission) {
		try {
			TastingHandler::update($tastingNumber, Input::all(), $tastingSession, $commission);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.session/retaste', [
					    'tastingsession' => $tastingSession->id,
					    'tastingnumber' => $tastingNumber->id,
					    'commission' => $commission->id,
					])->withErrors($ve->getErrors())
					->withInput();
		}
		return Redirect::route('tasting.session/show', [
			    'tastingsession' => $tastingSession->id,
		]);
	}

}
