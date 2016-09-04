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

use App\Contracts\MasterDataStore;
use App\Contracts\TastingHandler;
use App\Http\Controllers\BaseController;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\TastingStage;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Process\Exception\InvalidArgumentException;
use function view;

class CompetitionController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var TastingHandler */
	private $tastingHandler;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $viewFactory;

	/**
	 * @param MasterDataStore $masterDataStore
	 * @param TastingHandler $tastingHandler
	 * @param AuthManager $auth
	 * @param Factory $viewFactory
	 */
	public function __construct(MasterDataStore $masterDataStore, TastingHandler $tastingHandler, AuthManager $auth,
		Factory $viewFactory) {
		$this->masterDataStore = $masterDataStore;
		$this->tastingHandler = $tastingHandler;
		$this->auth = $auth;
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Show list of all competitions
	 * 
	 * @return Response
	 */
	public function index() {
		$user = $this->auth->user();
		$competitions = $this->masterDataStore->getCompetitions($user);

		return $this->viewFactory->make('settings/competition/index', [
				'competitions' => $competitions,
		]);
	}

	/**
	 * Show specified competitions
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function show(Competition $competition) {
		return $this->viewFactory->make('competition/show', [
			'competition' => $competition,
			'competitionStates' => CompetitionState::all(),
			'wines' => $competition->wines()->count(),
			'winesWithNr' => $competition->wines()->whereNotNull('nr')->count(),
			'winesTasted1' => $competition->wine_details()->whereNotNull('rating1')->count(),
			'winesTasted2' => $competition->wine_details()->kdb()->whereNotNull('rating2')->count(),
			'winesKdb' => $competition->wines()->kdb()->count(),
			'winesExcluded' => $competition->wines()->excluded()->count(),
			'winesTastingNumber1' => $competition->wines()->withTastingNumber(TastingStage::find(1))->count(),
			'winesTastingNumber2' => $competition->wines()->withTastingNumber(TastingStage::find(2))->count(),
			'winesSosi' => $competition->wines()->sosi()->count(),
			'winesChosen' => $competition->wines()->chosen()->count(),
		]);
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
		return $this->viewFactory->make('competition/complete-tasting', [
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
	public function lockTasting(Competition $competition, $tasting) {
		$this->authorize('complete-competition-tasting');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			$this->tastingHandler->lockTasting($competition, $tasting);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		return $this->viewFactory->make('competition/complete-kdb')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			$this->tastingHandler->lockKdb($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeExcluded(Competition $competition) {
		$this->authorize('complete-competition-excluded');

		return $this->viewFactory->make('competition/complete-excluded', [
			'data' => $competition,
		]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockExcluded(Competition $competition) {
		$this->authorize('complete-competition-excluded');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			$this->tastingHandler->lockExcluded($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeSosi(Competition $competition) {
		$this->authorize('complete-competition-sosi');

		return $this->viewFactory->make('competition/complete-sosi')
				->withData($competition);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockSosi(Competition $competition) {
		$this->authorize('complete-competition-sosi');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			$this->tastingHandler->lockSosi($competition);
		}
		return Redirect::route('competition/show', ['competiion' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function completeChoosing(Competition $competition) {
		$this->authorize('complete-competition-choosing');

		return $this->viewFactory->make('competition/complete-choosing', [
			'data' => $competition,
		]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockChoosing(Competition $competition) {
		$this->authorize('complete-competition-tasting-numbers');

		if (Input::has('del') && Input::get('del') == 'Ja') {
			$this->tastingHandler->lockChoosing($competition);
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

		return $this->viewFactory->make('competition/complete-tastingnumbers', [
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
			$this->tastingHandler->lockTastingNumbers($competition, $tasting);
			return Redirect::route('competition/show', [
					'competition' => $competition->id
			]);
		}
		return Redirect::route('tasting.numbers', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return type
	 */
	public function getReset(Competition $competition) {
		$this->authorize('reset-competition', $competition);

		return $this->viewFactory->make('settings/competition/reset');
	}

	/**
	 * @param Competition $competition
	 * @return type
	 */
	public function postReset(Competition $competition) {
		$this->authorize('reset-competition', $competition);

		if (Input::has('reset') && Input::get('reset') === 'Ja') {
			$this->masterDataStore->resetCompetition($competition);
		}
		return Redirect::route('settings.competitions');
	}

}
