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

namespace App\Wine;

use App\Contracts\WineHandler;
use App\Database\Repositories\WineRepository;
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Wine;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use PHPExcel_IOFactory;
use SebastianBergmann\RecursionContext\Exception;

class Handler implements WineHandler {

	/** @var WineRepository */
	private $wineRepository;

	public function __construct(WineRepository $wineRepository) {
		$this->wineRepository = $wineRepository;
	}

	/**
	 * Create a new wine
	 * 
	 * @param array $data
	 * @param Competition $competition
	 * @return Wine
	 */
	public function create(array $data, Competition $competition) {
		//allow short format for year field: YY -> 20YY
		if (isset($data['vintage']) && ctype_digit($data['vintage']) && $data['vintage'] < 99) {
			$data['vintage'] += 2000;
		}

		$validator = new WineValidator($data);
		$validator->setCompetition($competition);
		$validator->setUser(Auth::user());
		$validator->validateCreate();
		$wine = new Wine($data);

		//associate competition
		$wine->competition()->associate($competition);
		$wine->save();
		//ActivityLogger::log('Wein [' . $wine->nr . '] bei Bewerb [' . $competition->label . '] erstellt');
		return $wine;
	}

	/**
	 * Update the wine
	 * 
	 * @param Wine $wine
	 * @param array $data
	 * @param Competition $competition
	 * @return Wine
	 */
	public function update(Wine $wine, array $data) {
		//allow short format for year field: YY -> 20YY
		if (isset($data['vintage']) && ctype_digit($data['vintage']) && $data['vintage'] < 99) {
			$data['vintage'] += 2000;
		}

		$validator = new WineValidator($data, $wine);
		$validator->setCompetition($wine->competition);
		$validator->setUser(Auth::user());
		$validator->validateUpdate();

		$competitionState = $wine->competition->competitionState;
		if (isset($data['kdb']) && $wine->kdb !== $data['kdb'] && $competitionState->is(CompetitionState::STATE_KDB)) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['sosi']) && $wine->sosi !== $data['sosi'] && $competitionState->is(CompetitionState::STATE_SOSI)) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['chosen']) && $wine->chosen !== $data['chosen'] && $competitionState->is(CompetitionState::STATE_CHOOSE)) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['excluded']) && $wine->excluded !== $data['excluded'] && $competitionState->is(CompetitionState::STATE_EXCLUDE)) {
			throw new InvalidCompetitionStateException();
		}

		$this->wineRepository->update($wine, $data);
		//ActivityLogger::log('Wein [' . $wine->nr . '] bei Bewerb [' . $competition->label . '] bearbeitet');
		return $wine;
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 */
	public function updateKdb(Wine $wine, array $data) {
		$validator = Validator::make($data, array('value' => 'required|boolean'));
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}

		$this->wineRepository->update($wine, [
			'kdb' => $data['value'],
		]);
	}

	/**
	 * Import kdb wines using a file
	 * 
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int Number of read lines
	 */
	public function importKdb(UploadedFile $file, Competition $competition) {
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
				if (!isset($row[0])) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$wine = $competition->wines()->where('nr', '=', $row[0])->first();
				if (is_null($wine)) {
					Log::error('invalid wine id while importing kdb');
					throw new ValidationException(new MessageBag(array('Wein ' . $row[0] . ' nicht vorhanden')));
				}
				$this->updateKdb($wine, array(
					'value' => true,
				));
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

	/**
	 * 
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 */
	public function updateExcluded(Wine $wine, array $data) {
		$validator = \Validator::make($data, array('value' => 'required|boolean'));
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}
		$this->wineRepository->update($wine, [
			'excluded' => $data['value'],
		]);
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 */
	public function updateSosi(Wine $wine, array $data) {
		$validator = \Validator::make($data, array('value' => 'required|boolean'));
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}

		$this->wineRepository->update($wine, [
			'sosi' => $data['value']
		]);
	}

	/**
	 * Import sosi wines using a file
	 * 
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int Number of read lines
	 */
	public function importSosi(UploadedFile $file, Competition $competition) {
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
				if (!isset($row[0])) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$wine = $competition->wines()->where('nr', '=', $row[0])->first();
				if (is_null($wine)) {
					Log::error('invalid wine id while importing sosi');
					throw new ValidationException(new MessageBag(array('Wein ' . $row[0] . ' nicht vorhanden')));
				}
				if (!$wine->kdb) {
					Log::error('non kdb wine while importing sosi');
					throw new ValidationException(new MessageBag(array('Wein ' . $row[0] . ' ist kein KdB')));
				}
				$this->updateSosi($wine, array(
					'value' => true,
				));
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

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @throws ValidationException
	 */
	public function updateChosen(Wine $wine, array $data) {
		$validator = Validator::make($data, array('value' => 'required|boolean'));
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}

		$this->wineRepository->update($wine, [
			'chosen' => $data['value'],
		]);
	}

	/**
	 * Import chosen wines using a file
	 * 
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int Number of read lines
	 */
	public function importChosen(UploadedFile $file, Competition $competition) {
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
			$competition->wines()->update(array('chosen' => false));
			$rowCount = 1;

			foreach ($sheet->toArray() as $row) {
				if (!isset($row[0])) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$wine = $competition->wines()->where('nr', '=', $row[0])->first();
				if (is_null($wine)) {
					Log::error('invalid wine id while importing chosen');
					throw new ValidationException(new MessageBag(array('Wein ' . $row[0] . ' nicht vorhanden')));
				}
				$this->updateChosen($wine, array(
					'value' => true,
				));
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

	/**
	 * Delete the wine
	 * 
	 * @param Wine $wine
	 */
	public function delete(Wine $wine) {
		$this->wineRepository->delete($wine);
		//ActivityLogger::log('Wein [' . $wine->nr . '] von Bewerb [' . $wine->competition->label . '] gel&ouml;scht');
	}

	public function getUsersWines(User $user, Competition $competition) {
		if ($user->isAdmin()) {
			return $this->wineRepository->findAll($competition);
		} else {
			return $this->wineRepository->findUsersWines($user, $competition);
		}
	}

}
