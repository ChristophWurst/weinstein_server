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

use App\Contracts\TastingHandler;
use App\Exceptions\ValidationException;
use App\Http\Controllers\BaseController;
use App\Tasting\Commission;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class TastingController extends BaseController {

	/** @var TastingHandler */
	private $tastingHandler;

	/** @var Factory */
	private $view;

	/**
	 * @param TastingHandler $tastingHandler
	 * @param Factory $view
	 */
	public function __construct(TastingHandler $tastingHandler, Factory $view) {
		$this->tastingHandler = $tastingHandler;
		$this->view = $view;
	}

	/**
	 * Add tasting results
	 * 
	 * @param TastingSession $tastingSession
	 * @return Response
	 */
	public function add(TastingSession $tastingSession) {
		$this->authorize('create-tasting', $tastingSession);

		return $this->view->make('competition/tasting/tasting-session/tasting/form', [
				'competition' => $tastingSession->competition,
				'tastingSession' => $tastingSession,
				'tastingNumbers' => $this->tastingHandler->getNextTastingNumbers($tastingSession),
		]);
	}

	/**
	 * Validate and store tasting results
	 * 
	 * @param TastingSession $tastingSession
	 * @param Request $request
	 * @return Response
	 */
	public function store(TastingSession $tastingSession, Request $request) {
		$this->authorize('create-tasting', $tastingSession);

		try {
			$data = $request->all();
			$this->tastingHandler->createTasting($data, $tastingSession);
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
		if (!$this->tastingHandler->isTastingNumberTasted($tastingNumber)) {
			Log::error('cannot retaste' . $tastingNumber->id . ', it has not yet been tasted');
			App::abort(500);
		}
		return $this->view->make('competition/tasting/tasting-session/tasting/form', [
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
	 * @param Request $request
	 * @return type
	 */
	public function update(TastingSession $tastingSession, TastingNumber $tastingNumber, Commission $commission, Request $request) {
		$this->authorize('edit-tasting', [$tastingSession, $commission, $tastingNumber]);

		try {
			$data = $request->all();
			$this->tastingHandler->updateTasting($data, $tastingNumber, $tastingSession, $commission);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.session/retaste',
						[
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
