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
use App\Contracts\TastingCatalogueHandler;
use App\Contracts\TastingHandler;
use App\Http\Controllers\BaseController;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\TastingStage;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\Process\Exception\InvalidArgumentException;

class CompetitionController extends BaseController {

	/** @var MasterDataStore */
	private $masterDataStore;

	/** @var TastingHandler */
	private $tastingHandler;

	/** @var TastingCatalogueHandler */
	private $tastingCatalogueHandler;

	/** @var AuthManager */
	private $auth;

	/** @var Factory */
	private $view;

	/**
	 * @param MasterDataStore $masterDataStore
	 * @param TastingHandler $tastingHandler
	 * @param TastingCatalogueHandler $tastingCatalogueHandler
	 * @param AuthManager $auth
	 * @param Factory $view
	 */
	public function __construct(MasterDataStore $masterDataStore, TastingHandler $tastingHandler,
		TastingCatalogueHandler $tastingCatalogueHandler, AuthManager $auth, Factory $view) {
		$this->masterDataStore = $masterDataStore;
		$this->tastingHandler = $tastingHandler;
		$this->tastingCatalogueHandler = $tastingCatalogueHandler;
		$this->auth = $auth;
		$this->view = $view;
	}

	/**
	 * Show list of all competitions
	 * 
	 * @return View
	 */
	public function index() {
		$user = $this->auth->user();
		$competitions = $this->masterDataStore->getCompetitions($user);

		return $this->view->make('settings/competition/index', [
				'competitions' => $competitions,
		]);
	}

	/**
	 * Show specified competitions
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function show(Competition $competition) {
		return $this->view->make('competition/show',
				[
				'isCompetitionAdmin' => $competition->administrates($this->auth->user()),
				'competition' => $competition,
				'competition_states' => CompetitionState::all(),
				'wines' => $competition->wines()->count(),
				'wines_with_nr' => $competition->wines()->whereNotNull('nr')->count(),
				'wines_tasted1' => $competition->wine_details()->whereNotNull('rating1')->count(),
				'wines_tasted2' => $competition->wine_details()->whereNotNull('rating2')->count(),
				'wines_kdb' => $competition->wines()->kdb()->count(),
				'wines_excluded' => $competition->wines()->excluded()->count(),
				'wines_tasting_number1' => $competition->wines()->withTastingNumber(TastingStage::find(1))->count(),
				'wines_tasting_number2' => $competition->wines()->withTastingNumber(TastingStage::find(2))->count(),
				'wines_sosi' => $competition->wines()->sosi()->count(),
				'wines_chosen' => $competition->wines()->chosen()->count(),
				'wines_without_catalogue_number' => $this->tastingCatalogueHandler->getNrOfWinesWithoutCatalogueNumber($competition),
		]);
	}

	/**
	 * Show complete/lock confirmation page for specified tasting
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return View
	 * @throws InvalidArgumentException
	 */
	public function completeTasting(Competition $competition, $tasting) {
		$this->authorize('complete-competition-tasting-numbers');

		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}
		return $this->view->make('competition/complete-tasting', [
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
	public function lockTasting(Competition $competition, $tasting, Request $request) {
		$this->authorize('complete-competition-tasting');

		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}
		if ($request->has('del') && $request->get('del') === 'Ja') {
			$this->tastingHandler->lockTasting($competition, $tasting);
		}
		return Redirect::route('competition/show', [
				'competition' => $competition->id
		]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function completeKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		return $this->view->make('competition/complete-kdb', [
				'data' => $competition,
		]);
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function lockKdb(Competition $competition) {
		$this->authorize('complete-competition-kdb');

		if (Input::has('del') && Input::get('del') === 'Ja') {
			$this->tastingHandler->lockKdb($competition);
		}
		return Redirect::route('competition/show', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function completeExcluded(Competition $competition) {
		$this->authorize('complete-competition-excluded');

		return $this->view->make('competition/complete-excluded', [
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
		return Redirect::route('competition/show', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function completeSosi(Competition $competition) {
		$this->authorize('complete-competition-sosi');

		return $this->view->make('competition/complete-sosi', [
				'competition' => $competition,
		]);
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
		return Redirect::route('competition/show', ['competition' => $competition->id]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function completeChoosing(Competition $competition) {
		$this->authorize('complete-competition-choosing');

		return $this->view->make('competition/complete-choosing', [
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
		return Redirect::route('competition/show', ['competition' => $competition->id]);
	}

	/**
	 * Show complete/lock confirmation page for specified tasting
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @return View
	 * @throws InvalidArgumentException
	 */
	public function completeTastingNumbers(Competition $competition, $tasting) {
		$this->authorize('complete-competition-tasting-numbers');

		if (!in_array($tasting, [1, 2])) {
			throw new InvalidArgumentException();
		}

		return $this->view->make('competition/complete-tastingnumbers',
				[
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
	public function lockTastingNumbers(Competition $competition, $tasting): RedirectResponse {
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
	 * Show complete/lock confirmation page for specified tasting
	 * 
	 * @param Competition $competition
	 * @return View
	 */
	public function completeCatalogueNumbers(Competition $competition): View {
		$this->authorize('complete-competition-catalogue-numbers');

		return $this->view->make('competition/complete-catalogue-numbers',
				[
				'data' => $competition,
		]);
	}

	/**
	 * @throws InvalidArgumentException
	 * @param Competition $competition
	 * @return RedirectResponse
	 */
	public function lockCatalogueNumbers(Competition $competition, Request $request): RedirectResponse {
		$this->authorize('complete-competition-catalogue-numbers');

		if ($request->has('del') && $request->get('del') === 'Ja') {
			$this->tastingCatalogueHandler->finishAssignment($competition);
			return Redirect::route('competition/show', [
					'competition' => $competition,
			]);
		}
		return Redirect::route('enrollment.wines', [
				'competition' => $competition,
		]);
	}

	/**
	 * @param Competition $competition
	 * @return View
	 */
	public function getReset(Competition $competition): View {
		$this->authorize('reset-competition', $competition);

		return $this->view->make('settings/competition/reset');
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function postReset(Competition $competition) {
		$this->authorize('reset-competition', $competition);

		if (Input::has('reset') && Input::get('reset') === 'Ja') {
			$this->masterDataStore->resetCompetition($competition);
		}
		return Redirect::route('settings.competitions');
	}

}
