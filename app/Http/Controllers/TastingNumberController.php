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
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\Http\Controllers\BaseController;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\TastingNumber;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class TastingNumberController extends BaseController {

	/** @var TastingHandler */
	private $tastingHandler;

	/** @var Factory */
	private $viewFactory;

	/**
	 * @param TastingHandler $tastingHandler
	 * @param Factory $viewFactory
	 */
	public function __construct(TastingHandler $tastingHandler, Factory $viewFactory) {
		$this->tastingHandler = $tastingHandler;
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Show list of all assigned tasting numbers
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function index(Competition $competition) {
		$this->authorize('show-tastingnumbers', $competition);

		$tastingStage = $competition->getTastingStage();
		if (is_null($tastingStage)) {
			throw new InvalidCompetitionStateException();
		}

		$showAdd = false;
		$showComplete = false;
		$left = -1;
		$showReset = false;
		if ($competition->competitionState->id === CompetitionState::STATE_ENROLLMENT) {
			$showAdd = true;
		} else if ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS1) {
			$left = $competition->wines()->count() - $competition->wines()->withTastingNumber($tastingStage)->count();
			$showComplete = $left === 0;
			$showAdd = !$showComplete; //show add if not all wines are assigned
			$showReset = true;
		} else if ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			// there is no check (for now)
			// kdb wines do not have to be tasted a second time
			$kdbWines = $competition->wines()->kdb()->count();
			$tastingNumber2 = $competition->wines()->withTastingNumber($tastingStage)->count();
			$showAdd = true; // always show, because even non-kdb wines can be tasted a second time
			$showComplete = true;
			$showReset = true;
		}

		return $this->viewFactory->make('competition/tasting/tasting-number/index',
				[
				'competition' => $competition,
				'numbers' => $this->tastingHandler->getAllTastingNumbers($competition, $tastingStage),
				'show_add' => $showAdd,
				'show_reset' => $showReset,
				'left' => $left,
				'finished' => $showComplete,
		]);
	}

	/**
	 * Assign new tasting number
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function assign(Competition $competition) {
		$this->authorize('assign-tastingnumber');

		return $this->viewFactory->make('competition/tasting/tasting-number/form')
				->with('competition', $competition);
	}

	/**
	 * Validate and store newly assigned tasting number
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function store(Competition $competition) {
		$this->authorize('assign-tastingnumber');

		$tastingStage = $competition->getTastingStage();
		if (is_null($tastingStage)) {
			throw new InvalidCompetitionStateException();
		}

		try {
			$data = Input::all();
			$this->tastingHandler->createTastingNumber($data, $competition);
		} catch (ValidationException $ve) {
			return Redirect::route('tasting.numbers/assign', ['competition' => $competition->id])
					->withErrors($ve->getErrors())
					->withInput();
		}
		//if all wines are assigned, redirect to the list index page
		if (in_array($competition->competitionState->id,
				[CompetitionState::STATE_ENROLLMENT, CompetitionState::STATE_TASTINGNUMBERS1])) {
			$allWines = $competition->wines()->count();
			$assigned = $competition->wines()->withTastingNumber($tastingStage)->count();
		} elseif ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			$allWines = $competition->wines()->kdb()->count();
			$assigned = $competition->wines()->kdb()->withTastingNumber($tastingStage)->count();
		} else {
			throw Exception('invalid application state, should be TASTINGNUMBERS1 or TASTINGNUMBERS2');
		}
		if ($assigned === $allWines) {
			return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
		}
		return Redirect::route('tasting.numbers/assign', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function resetForm(Competition $competition) {
		$this->authorize('unassign-tastingnumber', $competition);

		return $this->viewFactory->make('competition/tasting/tasting-number/reset')
				->with('competition', $competition);
	}

	/**
	 * @param Competition $competition
	 * @return RedirectResponse
	 */
	public function reset(Competition $competition) {
		$this->authorize('unassign-tastingnumber', $competition);

		if (Input::get('reset') == 'Ja') {
			$this->tastingHandler->resetTastingNumbers($competition);
		}
		return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
	}

	/**
	 * Ask user about deallocating the specified tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @return View
	 */
	public function deallocate(TastingNumber $tastingNumber) {
		$this->authorize('unassign-tastingnumber');

		return $this->viewFactory->make('competition/tasting/tasting-number/deallocate', [
				'data' => $tastingNumber
			])->with('competition', $tastingNumber->wine->competition);
	}

	/**
	 * Check users choice and eventually delete specified tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @return Response
	 */
	public function delete(TastingNumber $tastingNumber) {
		$this->authorize('unassign-tastingnumber');

		if (Input::get('del') == 'Ja') {
			$this->tastingHandler->deleteTastingNumber($tastingNumber);
		}
		return Redirect::route('tasting.numbers', ['competition' => $tastingNumber->wine->competition->id]);
	}

	/**
	 * Show import form
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function import(Competition $competition) {
		$this->authorize('import-tastingnumbers');

		return $this->viewFactory->make('competition/tasting/tasting-number/import')
				->with('competition', $competition);
	}

	/**
	 * Validate and store import files tasting numbers
	 * 
	 * @param Competition $competition
	 * @return RedirectResponse
	 */
	public function importStore(Competition $competition) {
		$this->authorize('import-tastingnumbers');

		try {
			$file = Input::file('xlsfile');
			if ($file === null) {
				return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
			}
			$rowsImported = $this->tastingHandler->importTastingNumbers($file, $competition);
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
		$this->authorize('translate-tastingnumber', $competition);

		$tastingStage = $competition->getTastingStage();
		if (is_null($tastingStage)) {
			throw new InvalidCompetitionStateException();
		}

		$tastingNumber = $competition
			->tastingnumbers()
			->where('tastingnumber.nr', '=', $id)
			->where('tastingstage_id', '=', $tastingStage->id)
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
