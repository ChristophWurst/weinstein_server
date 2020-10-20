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
use App\Exceptions\WineLockedException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Validation\WineValidatorFactory;
use App\Wine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use PhpOffice\PhpSpreadsheet\IOFactory;
use function implode;
use function in_array;
use function is_array;
use function json_decode;

class Handler implements WineHandler {

	/** @var WineRepository */
	private $wineRepository;

	/** @var WineValidatorFactory */
	private $validatorFactory;

	public function __construct(WineRepository $wineRepository, WineValidatorFactory $validatorFactory) {
		$this->wineRepository = $wineRepository;
		$this->validatorFactory = $validatorFactory;
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

		$sort = $wine->winesort;
		$qualitiesAllowed = json_decode($sort->quality_allowed, false);
		if (is_array($qualitiesAllowed) && !empty($qualitiesAllowed) && !in_array($wine->winequality_id, $qualitiesAllowed)) {
			throw new ValidationException(new MessageBag([
				'Ungültige Kombination von Sorte und Qualitätsstufe. Zulässige Qualitätsstufe(n) ist/sind: ' . implode(',', $qualitiesAllowed),
			]));
		}

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
	 * @throws ValidationException
	 * @throws InvalidCompetitionStateException if the user (non admin) is not allowed to edit wines in that state
	 * @return Wine
	 */
	public function update(Wine $wine, array $data) {
		$competitionState = $wine->competition->competitionState;

		//allow short format for year field: YY -> 20YY
		if (isset($data['vintage']) && ctype_digit($data['vintage']) && $data['vintage'] < 99) {
			$data['vintage'] += 2000;
		}
		// Validate and update approval nr only during enrollment
		// TODO: refactor this logic into better permission/state checks
		if (array_key_exists('approvalnr', $data) && $competitionState->id > CompetitionState::STATE_ENROLLMENT) {
			unset($data['approvalnr']);
		}

		$validator = $this->validatorFactory->newWineValidator($wine, $data);
		$validator->setCompetition($wine->competition);
		$validator->setUser(Auth::user());
		$validator->validateUpdate();

		if (isset($data['kdb']) && $wine->kdb !== $data['kdb'] && $competitionState->id !== CompetitionState::STATE_KDB) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['sosi']) && $wine->sosi !== $data['sosi'] && $competitionState->id !== CompetitionState::STATE_SOSI) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['chosen']) && $wine->chosen != $data['chosen'] && $competitionState->id !== CompetitionState::STATE_CHOOSE) {
			throw new InvalidCompetitionStateException();
		}
		if (isset($data['excluded']) && $wine->excluded !== $data['excluded'] && $competitionState->id !== CompetitionState::STATE_EXCLUDE) {
			throw new InvalidCompetitionStateException();
		}

		$wine->fill($data);
		$enrollmentAttributes = array_diff(array_keys($data), [
			'kdb',
			'sosi',
			'chosen',
			'excluded',
		]);
		if ($wine->isDirty($enrollmentAttributes)) {
			// These attributes may only be changed via enrollment while 'nr' is not set or by an admin
			if ($competitionState->id !== CompetitionState::STATE_ENROLLMENT && !Auth::user()->isAdmin()) {
				throw new WineLockedException();
			}
			if ($competitionState->id === CompetitionState::STATE_ENROLLMENT && !is_null($wine->nr) && !Auth::user()->isAdmin()) {
				throw new WineLockedException();
			}
		}
		if ($competitionState->id === CompetitionState::STATE_CHOOSE) {
			if (($wine->kdb || $wine->sosi) &&
				isset($data['chosen']) && !$data['chosen']) {
				throw new AuthorizationException("KdB und SoSi müssen ausgeschenkt werden");
			}
			if ($wine->competition->wines_chosen_signed_off()
				->where('association_id', $wine->applicant->association->id)
				->exists()) {
				// Chosen wines of this association have already been signed off
				throw new AuthorizationException("Vereinsadmin hat die Auswahl bereits abgeschlossen");
			}
		}
		if ($competitionState->id === CompetitionState::STATE_EXCLUDE &&
			isset($data['excluded']) && $data['excluded']) {
			// Excluded wines must not be chosen, hence we have to update the default value here
			$data['chosen'] = false;
		}
		if ($competitionState->id === CompetitionState::STATE_CHOOSE &&
			$wine->excluded &&
			isset($data['chosen']) && $data['chosen']) {
			throw new AuthorizationException("Dieser Wein darf nicht ausgeschenkt werden");
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
			$doc = IOFactory::load($file->getRealPath());
		} catch (\InvalidArgumentException $ex) {
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
	 * Import excluded wines using a file
	 * 
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int Number of read lines
	 */
	public function importExcluded(UploadedFile $file, Competition $competition) {
		//iterate over all entries and try to store them
		//
		//if exceptions occur, all db actions are rolled back to prevent data 
		//inconsistency
		try {
			$doc = IOFactory::load($file->getRealPath());
		} catch (\InvalidArgumentException $ex) {
			throw new ValidationException(new MessageBag(array('Ung&uuml;ltiges Dateiformat')));
		}

		$sheet = $doc->getActiveSheet();

		DB::beginTransaction();
		$rowCount = 0;
		try {
			$competition->wines()->update([
				'excluded' => false,
				'chosen' => true,
			]);
			$rowCount++;

			foreach ($sheet->toArray() as $row) {
				if (!isset($row[0])) {
					Log::error('invalid excluded import file format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$wine = $competition->wines()->where('nr', '=', $row[0])->first();
				if (is_null($wine)) {
					Log::error('invalid wine id while importing excluded');
					throw new ValidationException(new MessageBag(array('Wein ' . $row[0] . ' nicht vorhanden')));
				}
				$this->wineRepository->update($wine, [
					'excluded' => true,
					'chosen' => false,
				]);
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
			$doc = IOFactory::load($file->getRealPath());
		} catch (\InvalidArgumentException $ex) {
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
	 * @param array $chosen
	 * @throws ValidationException
	 */
	private function updateChosen(Wine $wine, bool $chosen) {
		if (($wine->kdb || $wine->sosi) && !$chosen) {
			throw new AuthorizationException();
		}

		$this->wineRepository->update($wine, [
			'chosen' => $chosen,
		]);
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
