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

namespace App\Tasting;

use App\Contracts\TastingHandler;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\MasterData\Competition;
use Exception;
use InvalidArgumentException;
use Weinstein\Competition\TastingNumber\TastingNumberHandler;

class Handler implements TastingHandler {

	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var TastingSessionRepository */
	private $tastingSessionRepository;

	public function __construct(CompetitionRepository $competitionRepository,
		TastingSessionRepository $tastingSessionRepository) {
		$this->competitionRepository = $competitionRepository;
		$this->tastingSessionRepository = $tastingSessionRepository;
	}

	public function lockTastingNumbers(Competition $competition, $tasting) {
		if (!in_array($tasting, array(1, 2))) {
			throw new InvalidArgumentException();
		}
		if (in_array($competition->competitionstate->description, [
				'TASTINGNUMBERS1',
				'TASTINGNUMBERS2'
			])) {
			$competition->competitionstate_id += 1;
			$this->competitionRepository->update($competition);
		} else {
			throw new Exception('invalid competition state');
		}

		//close all sessions
		foreach ($competition->tastingsessions as $session) {
			$session->locked = true;
			$this->tastingSessionRepository->update($session);
		}
		//ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $tasting . '. Kostnummernvergabe beendet');
	}

	public function lockTasting(Competition $competition, $tasting) {
		if (!in_array($tasting, array(1, 2))) {
			throw new InvalidArgumentException();
		}
		$state = $competition->competitionstate->description;
		if ($competition->competitionstate->description == 'TASTING1') {
			$competition->competitionstate_id += 1;
			$this->competitionRepository->update($competition);
		} elseif ($competition->competitionstate->description == 'TASTING2') {
			$competition->competitionstate_id += 1;
			$this->competitionRepository->update($competition);
		} else {
			throw new Exception('invalid competition state');
		}

		// close all sessions
		foreach ($competition->tastingsessions as $session) {
			$session->locked = true;
			$this->tastingSessionRepository->update($session);
		}
		$state = $state == 'TASTING1' ? 1 : 2;
		//ActivityLogger::log('Bewerb [' . $competition->label . '] ' . $state . '. Verkostung beendet');
	}

	public function lockKdb(Competition $competition) {
		$competition->competitionstate_id += 1;
		$this->competitionRepository->update($competition);
		//ActivityLogger::log('Bewerb [' . $competition->label . '] KdB Zuweisung beendet');
	}

	public function lockExcluded(Competition $competition) {
		$competition->competitionstate_id += 1;
		$this->competitionRepository->update($competition);
		//ActivityLogger::log('Bewerb [' . $competition->label . '] Ausschluss beendet');
	}

	public function lockSosi(Competition $competition) {
		$competition->competitionstate_id += 1;
		$this->competitionRepository->update($competition);
		//ActivityLogger::log('Bewerb [' . $competition->label . '] SoSi Zuweisung beendet');
	}

	public function lockChoosing(Competition $competition) {
		$competition->competitionstate_id += 1;
		$this->competitionRepository->update($competition);
		//ActivityLogger::log('Bewerb [' . $competition->label . '] Auswahl beendet');
	}

	public function isTastingFinished(Competition $competition) {
		return TastingNumberHandler::getUntasted($competition, $competition->getTastingStage())->count() === 0;
	}

}
