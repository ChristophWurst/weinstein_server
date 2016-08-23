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
use App\Database\Repositories\CommissionRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\TastingNumberRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\Database\Repositories\WineRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use InvalidArgumentException;
use PHPExcel_IOFactory;
use Weinstein\Competition\TastingSession\Taster\TasterHandler;

class Handler implements TastingHandler {

	/** @var CommissionRepository */
	private $commissionRepository;

	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var TastingNumberRepository */
	private $tastingNumberRepository;

	/** @var TastingSessionRepository */
	private $tastingSessionRepository;

	/** @var WineRepository */
	private $wineRepository;

	public function __construct(CommissionRepository $commissionRepository, CompetitionRepository $competitionRepository,
		TastingNumberRepository $tastingNumberRepository, TastingSessionRepository $tastingSessionRepository,
		WineRepository $wineRepository) {
		$this->competitionRepository = $competitionRepository;
		$this->tastingNumberRepository = $tastingNumberRepository;
		$this->tastingSessionRepository = $tastingSessionRepository;
		$this->wineRepository = $wineRepository;
		$this->commissionRepository = $commissionRepository;
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

	public function getAllTastingSessions(Competition $competition, TastingStage $tastingStage,
		User $user = null) {
		if (is_null($user) || $user->isAdmin()) {
			return $this->tastingSessionRepository->findAll($competition, $tastingStage);
		}
		return $this->tastingSessionRepository->findForUser($competition, $tastingStage, $user);
	}

	public function createTastingSession(array $data, Competition $competition) {
		$validator = new TastingSessionValidator($data);
		$validator->setCompetition($competition);
		$validator->validateCreate();

		$data['nr'] = $competition->tastingsessions()->ofTastingStage($competition->getTastingStage())->max('nr') + 1;
		$tastingSession = $this->tastingSessionRepository->create($data, $competition, $competition->getTastingStage());

		$this->createCommissions($tastingSession, $data['commissions']);

		return $tastingSession;
	}

	/**
	 * Create nr commissions for the given tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @param int $nr
	 */
	private function createCommissions(TastingSession $tastingSession, $nr) {
		$dataA = [
			'side' => 'a',
		];
		$this->commissionRepository->create($dataA, $tastingSession);


		if ($nr == 2) {
			$dataB = [
				'side' => 'b',
			];
			$this->commissionRepository->create($dataB, $tastingSession);
		}
	}

	public function updateTastingSession(TastingSession $tastingSession, array $data) {
		$validator = new TastingSessionValidator($data, $tastingSession);
		$validator->setCompetition($tastingSession->competition);
		$validator->validateUpdate();

		$this->tastingSessionRepository->update($tastingSession, $data);
	}

	public function lockTastingSession(TastingSession $tastingSession) {
		$tastingSession->locked = true;
		$tastingSession->save(); // TODO: move to repo
	}

	public function deleteTastingSession(TastingSession $tastingSession) {
		$this->tastingSessionRepository->delete($tastingSession);
	}

	public function createTaster(array $data, TastingSession $tastingSession) {
		// TODO: move to repo
		$taster = TasterHandler::create($data, $tastingSession);
		$taster->active = true;
		$taster->save();
	}

	public function getTastingSessionTasters(TastingSession $tastingSession) {
		return \TasterHandler::getAll($tastingSession);
	}

}
