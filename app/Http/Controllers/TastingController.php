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

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\BaseController;
use App\MasterData\CompetitionState;
use App\Tasting\Commission;
use App\Tasting\TastingSession;
use App\Tasting\TastingNumber;
use Weinstein\Support\Facades\TastingHandlerFacade as TastingHandler;
use App\Exceptions\ValidationException;

class TastingController extends BaseController {

	/**
	 * Add tasting results
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function add(TastingSession $tastingSession) {
		$this->authorize('create-tasting', $tastingSession);

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
		$this->authorize('create-tasting', $tastingSession);

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
		$this->authorize('edit-tasting', [$tastingSession, $commission, $tastingNumber]);

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
		$this->authorize('edit-tasting', [$tastingSession, $commission, $tastingNumber]);

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
