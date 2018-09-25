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

namespace App\TastingCatalogue;

use App\Contracts\TastingCatalogueHandler;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\WineRepository;
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;

class CatalogueHandler implements TastingCatalogueHandler {

	/** @var DatabaseManager */
	private $db;

	/** @var WineRepository */
	private $wineRepository;

	/** @var CompetitionRepository */
	private $competitionRepository;

	/**
	 * @param DatabaseManager $db
	 * @param WineRepository $wineRepository
	 * @param CompetitionRepository $competitionRepository
	 */
	public function __construct(DatabaseManager $db, WineRepository $wineRepository,
		CompetitionRepository $competitionRepository) {
		$this->db = $db;
		$this->wineRepository = $wineRepository;
		$this->competitionRepository = $competitionRepository;
	}

	/**
	 * @param Competition $competition
	 * @return bool
	 */
	public function allWinesHaveBeenAssigned(Competition $competition): bool {
		return $this->allWinesHaveACatalogueNumber($competition);
	}

	public function getNrOfWinesWithoutCatalogueNumber(Competition $competition): int {
		return $this->wineRepository->getNumberOfWinesWithoutCatalogueNumber($competition);
	}

	protected function loadExcelFile(UploadedFile $file): PHPExcel {
		try {
			return PHPExcel_IOFactory::load($file->getRealPath());
		} catch (PHPExcel_Reader_Exception $ex) {
			throw new ValidationException(new MessageBag(array('Ung&uuml;ltiges Dateiformat')));
		}
	}

	private function rowHasData(array $row): bool {
		return (isset($row[0]) && isset($row[1]));
	}

	private function assignCatalgueNumberToWine(Competition $competition, int $wineNr, int $tastingNumber) {
		$wine = $this->wineRepository->findByNr($competition, $wineNr);
		if (is_null($wine)) {
			Log::error('invalid wine id while importing kdb');
			throw new ValidationException(new MessageBag(array('Wein ' . $wineNr . ' nicht vorhanden')));
		}
		if (!$wine->chosen) {
			throw new ValidationException(new MessageBag(array('Nur ausgeschenkte Weine werden in den Katalog aufgenommen')));
		}
		$this->wineRepository->update($wine, [
			'catalogue_number' => $tastingNumber,
		]);
	}

	private function resetCatalogueNumbers(Competition $competition) {
		$this->wineRepository->resetCatalogueNumbers($competition);
	}

	private function loadCatalogueNumbersFromSpreadSheet(Competition $competition, UploadedFile $file): int {
		$doc = $this->loadExcelFile($file);
		$sheet = $doc->getActiveSheet();
		$rowNr = 1;
		try {
			foreach ($sheet->toArray() as $row) {
				if (!$this->rowHasData($row)) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag([
					'Fehler beim Lesen der Datei',
					]));
				}
				$this->assignCatalgueNumberToWine($competition, $row[0], $row[1]);
				$rowNr++;
			}
		} catch (ValidationException $ve) {
			// Add erroneous line number hint
			$messages = new MessageBag([
				'row' => 'Fehler in Zeile ' . $rowNr,
			]);
			$messages->merge($ve->getErrors());
			throw new ValidationException($messages);
		}
		return $rowNr - 1;
	}

	private function allWinesHaveACatalogueNumber(Competition $competition): bool {
		return $this->wineRepository->getNumberOfWinesWithoutCatalogueNumber($competition) === 0;
	}

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int number of read lines
	 * @throws ValidationException
	 * @throws InvalidCompetitionStateException
	 */
	public function importCatalogueNumbers(UploadedFile $file, Competition $competition): int {
		if (!$competition->competitionState->id !== CompetitionState::STATE_CATALOGUE_NUMBERS) {
			throw new InvalidCompetitionStateException();
		}

		$dbConnection = $this->db->connection();
		$dbConnection->beginTransaction();

		try {
			$this->resetCatalogueNumbers($competition);
			$rowsImported = $this->loadCatalogueNumbersFromSpreadSheet($competition, $file);
			if (!$this->allWinesHaveACatalogueNumber($competition)) {
				throw new ValidationException(new MessageBag([
				'Unvollst&auml;ndiger Import: nicht allen Weinen wurde eine Katalognummer zugewiesen.',
				]));
			}
		} catch (ValidationException $ve) {
			$dbConnection->rollBack();
			throw $ve;
		}

		$dbConnection->commit();
		return $rowsImported;
	}

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function finishAssignment(Competition $competition) {
		if (!$this->allWinesHaveACatalogueNumber($competition)) {
			throw new \Exception('Invalid state. Not all wines have been assigned a catalogue number');
		}

		$competition->competition_state_id++;
		$this->competitionRepository->update($competition);
	}

}
