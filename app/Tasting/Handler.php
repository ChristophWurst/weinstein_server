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
use App\Database\Repositories\TastingNumberRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\Database\Repositories\WineRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;
use PHPExcel_IOFactory;

class Handler implements TastingHandler {

	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var TastingNumberRepository */
	private $tastingNumberRepository;

	/** @var TastingSessionRepository */
	private $tastingSessionRepository;

	/** @var WineRepository */
	private $wineRepository;

	public function __construct(CompetitionRepository $competitionRepository,
		TastingNumberRepository $tastingNumberRepository, TastingSessionRepository $tastingSessionRepository,
		WineRepository $wineRepository) {
		$this->competitionRepository = $competitionRepository;
		$this->tastingNumberRepository = $tastingNumberRepository;
		$this->tastingSessionRepository = $tastingSessionRepository;
		$this->wineRepository = $wineRepository;
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
		return $this->getUntastedTastingNumbers($competition, $competition->getTastingStage())->count() === 0;
	}

	public function createTastingNumber(array $data, Competition $competition) {
		$validator = new TastingNumberValidator($data);
		$validator->setCompetition($competition);
		$validator->validateCreate();

		$wine = $this->wineRepository->findByNr($competition, $data['wine_nr']);
		//competition's tasting stage is choosen by default
		$tastingStage = $competition->getTastingStage;

		return $this->tastingNumberRepository->create($data, $tastingStage, $wine);
	}

	public function importTastingNumbers(UploadedFile $file, Competition $competition) {
		//iterate over all entries and try to store them
		//if exceptions occur, all db actions are rolled back to prevent data 
		//inconsistency
		try {
			$doc = PHPExcel_IOFactory::load($file->getRealPath());
		} catch (Exception $ex) {
			throw new ValidationException(new MessageBag(array('Ung&uuml;ltiges Dateiformat')));
		}

		$sheet = $doc->getActiveSheet();

		DB::beginTransaction();
		try {
			$rowCount = 1;

			foreach ($sheet->toArray() as $row) {
				if (!isset($row[0]) || !isset($row[1])) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$data = array(
					'nr' => $row[0],
					'wine_nr' => $row[1],
				);
				$this->create($data, $competition);
				$rowCount++;
			}
		} catch (ValidationException $ve) {
			DB::rollback();
			$messages = new MessageBag(array(
				'row' => 'Fehler in Zeile ' . $rowCount,
			));
			$messages->merge($ve->getErrors());
			throw new ValidationException($messages);
		}
		DB::commit();
		//return number of read lines
		return $rowCount - 1;
	}

	public function deleteTastingNumber(TastingNumber $tastingNumber) {
		return $this->tastingNumberRepository->delete($tastingNumber);
	}

	public function getUntastedTastingNumbers(Competition $competition, TastingStage $tastingStage) {
		return $this->tastingNumberRepository->findUntasted($competition, $tastingStage);
	}

	public function getAllTastingNumbers(Competition $competition, TastingStage $tastingStage = null) {
		return $this->tastingNumberRepository->getAll($competition, $tastingStage);
	}

}
