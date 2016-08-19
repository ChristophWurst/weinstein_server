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

namespace Weinstein\Competition;

use ActivityLogger;
use App\Competition\Competition;
use App\Competition\CompetitionState;
use App\Competition\Tasting\TastingSession;
use App\Tasting\TastingStage;
use App\Competition\Wine\Wine;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CompetitionHandler {

	/**
	 * Competition data provider
	 * 
	 * @var CompetitionDataProvider
	 */
	private $dataProvider;

	/**
	 * Constructor
	 * 
	 * @param CompetitionDataProvider $dataProvider
	 */
	public function __construct(CompetitionDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Lock specified tasting (competition to next competition state)
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @throws InvalidArgumentException
	 */
	public function lockTasting(Competition $competition, $tasting) {
		if (!in_array($tasting, array(1, 2))) {
			throw new InvalidArgumentException();
		}
		$state = $competition->competitionstate->description;
		if ($competition->competitionstate->description == 'TASTING1') {
			$competition->competitionstate_id += 1;
			$competition->save();
		} elseif ($competition->competitionstate->description == 'TASTING2') {
			$competition->competitionstate_id += 1;
			$competition->save();
		} else {
			throw new Exception('invalid competition state');
		}

		//close all sessions
		foreach ($competition->tastingsessions as $session) {
			$session->locked = true;
			$session->save();
		}
		$state = $state == 'TASTING1' ? 1 : 2;
		ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $state . '. Verkostung beendet');
	}

	/**
	 * Lock specified tasting number inputs (competition to next competition state)
	 * 
	 * @param Competition $competition
	 * @param int $tasting
	 * @throws InvalidArgumentException
	 */
	public function lockTastingNumbers(Competition $competition, $tasting) {
		if (!in_array($tasting, array(1, 2))) {
			throw new InvalidArgumentException();
		}
		if (in_array($competition->competitionstate->description, array('TASTINGNUMBERS1', 'TASTINGNUMBERS2'))) {
			$competition->competitionstate_id += 1;
			$competition->save();
		} else {
			throw new Exception('invalid competition state');
		}

		//close all sessions
		foreach ($competition->tastingsessions as $session) {
			$session->locked = true;
			$session->save();
		}
		ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $tasting . '. Kostnummernvergabe beendet');
	}

	/**
	 * 
	 * @param Competition $competition
	 */
	public function lockKdb(Competition $competition) {
		$competition->competitionstate_id += 1;
		$competition->save();
		ActivityLogger::log('Bewerb [' . $competition->label . '] KdB Zuweisung beendet');
	}

	/**
	 * 
	 * @param Competition $competition
	 */
	public function lockExcluded(Competition $competition) {
		$competition->competitionstate_id += 1;
		$competition->save();
		ActivityLogger::log('Bewerb [' . $competition->label . '] Ausschluss beendet');
	}

	/**
	 * 
	 * @param Competition $competition
	 */
	public function lockSosi(Competition $competition) {
		$competition->competitionstate_id += 1;
		$competition->save();
		ActivityLogger::log('Bewerb [' . $competition->label . '] SoSi Zuweisung beendet');
	}

	/**
	 * 
	 * @param Competition $competition
	 */
	public function lockChoosing(Competition $competition) {
		$competition->competitionstate_id += 1;
		$competition->save();
		ActivityLogger::log('Bewerb [' . $competition->label . '] Auswahl beendet');
	}

	/**
	 * Add tasting session to competition
	 * 
	 * @param Competition $competition
	 * @param TastingSession $tastingSession
	 */
	public function addTastingSession(Competition $competition, TastingSession $tastingSession) {
		$competition->tastingsessions()->save($tastingSession);
	}

	/**
	 * Add wine to competition
	 * 
	 * @param Competition $competition
	 * @param Wine $wine
	 */
	public function addWine(Competition $competition, Wine $wine) {
		$competition->wines()->save($wine);
	}

	/**
	 * Get all competitions
	 * 
	 * @return Collection
	 */
	public function getAll() {
		return $this->dataProvider->getAll();
	}

	/**
	 * Get competitions wines
	 * 
	 * @param Competition $competition
	 * @return Collection
	 */
	public function getWines(Competition $competition) {
		return \WineHandler::getAll($competition);
	}

	/**
	 * Get competitions wine sorts
	 * 
	 * @param Competition $competition
	 * @return Collection
	 */
	public function getWineSorts(Competition $competition) {
		return \WineSortHandler::getAll($competition);
	}

	/**
	 * Check if tasting has finished or if there are remaining tasting numbers
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @return boolean
	 */
	public function tastingFinished(Competition $competition, TastingStage $tastingStage) {
		return \TastingNumberHandler::getUntasted($competition, $tastingStage)->count() === 0;
	}

	/**
	 * reset all competition data
	 * 
	 * @param Competition $competition
	 */
	public function reset(Competition $competition) {
		DB::transaction(function() use ($competition) {
			$competition->tastingsessions()->chunk(100, function($sessions) {
				foreach ($sessions as $session) {
					foreach ($session->commissions as $commission) {
						foreach ($commission->tasters as $taster) {
							$taster->tastings()->delete();
							$taster->delete();
						}
						$commission->delete();
					}
					$session->delete();
				}
			});
			$competition->wines()->chunk(100, function($wines) {
				foreach ($wines as $wine) {
					$wine->tastingnumbers()->delete();
					$wine->delete();
				}
			});

			//$competition->user()->associate(null);
			$competition->competitionstate()->associate(CompetitionState::find(CompetitionState::STATE_ENROLLMENT));
			$competition->save();
		});
		ActivityLogger::log('Bewerb [' . $competition->label . '] zur&uuml;ckgesetzt');
	}

}
