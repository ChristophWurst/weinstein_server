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

use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\TastingStage;
use App\Http\Controllers\BaseController;
use CompetitionHandler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Symfony\Component\Process\Exception\InvalidArgumentException;

class CompetitionController extends BaseController {

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
	 * @param Competition $competition
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
		$this->authorize('complete-competition-tasting-numbers');

		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}
		return View::make('competition/complete-tasting')
				->withData($competition)
				->withTasting($tasting);
	}

	/**
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function lockTasting(Competition $competition, $tasting) {
		$this->authorize('complete-competition-tasting');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockTasting($competition, $tasting);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		return View::make('competition/complete-kdb')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockKdb($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeExcluded(Competition $competition) {
		$this->authorize('complete-competition-excluded');

		return View::make('competition/complete-excluded')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockExcluded(Competition $competition) {
		$this->authorize('complete-competition-excluded');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockExcluded($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeSosi(Competition $competition) {
		$this->authorize('complete-competition-sosi');

		return View::make('competition/complete-sosi')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockSosi(Competition $competition) {
		$this->authorize('complete-competition-sosi');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockSosi($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeChoosing(Competition $competition) {
		$this->authorize('complete-competition-choosing');

		return View::make('competition/complete-choosing')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockChoosing(Competition $competition) {
		$this->authorize('complete-competition-tasting-numbers');

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
		$this->authorize('complete-competition-tasting-numbers');

		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}

		return View::make('competition/complete-tastingnumbers')->with([
				'data' => $competition,
				'tasting' => $tasting,
		]);
	}

	/**
	 * @param Competition $competition
	 * @param int $tasting
	 * @return Response
	 * @throws InvalidArgumentException
	 */
	public function lockTastingNumbers(Competition $competition, $tasting) {
		$this->authorize('complete-competition-tasting-numbers');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			CompetitionHandler::lockTastingNumbers($competition, $tasting);
			return Redirect::route('competition/show', ['competition' => $competition->id]);
		}
		return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return type
	 */
	public function getReset(Competition $competition) {
		$this->authorize('reset-competition', $competition);

		return View::make('settings/competition/reset');
	}

	/**
	 * @param Competition $competition
	 * @return type
	 */
	public function postReset(Competition $competition) {
		$this->authorize('reset-competition', $competition);

		if (Input::has('reset') && Input::get('reset') == 'Ja') {
			CompetitionHandler::reset($competition);
		}
		return Redirect::route('settings.competitions');
	}

}
