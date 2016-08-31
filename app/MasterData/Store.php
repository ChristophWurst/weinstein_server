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

namespace App\MasterData;

use App\Contracts\MasterDataStore;
use App\Database\Repositories\ApplicantRepository;
use App\Database\Repositories\AssociationRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\UserRepository;
use App\Database\Repositories\WineSortRepository;
use App\Exceptions\NotImplementedException;
use App\Exceptions\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use PHPExcel_IOFactory;
use function str_random;

class Store implements MasterDataStore {

	/** @var ApplicantRepository */
	private $applicantRepository;

	/** @var AssociationRepository */
	private $associationRepository;

	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var UserRepository */
	private $userRepository;

	/** @var WineSortRepository */
	private $wineSortRepository;

	public function __construct(ApplicantRepository $applicantRepository, AssociationRepository $associationRepository,
		CompetitionRepository $competitionRepository, UserRepository $userRepository, WineSortRepository $wineSortRepository) {
		$this->applicantRepository = $applicantRepository;
		$this->associationRepository = $associationRepository;
		$this->competitionRepository = $competitionRepository;
		$this->userRepository = $userRepository;
		$this->wineSortRepository = $wineSortRepository;
	}

	public function getApplicants(User $user = null) {
		if (is_null($user) || $user->isAdmin()) {
			return $this->applicantRepository->findAll();
		}
		return $this->applicantRepository->findForUser($user);
	}

	public function createApplicant(array $data) {
		$applicantValidationException = null;

		//validate applicant
		try {
			$applicantValidator = new ApplicantValidator($data);
			$applicantValidator->validateCreate();
		} catch (ValidationException $ve) {
			$applicantValidationException = $ve;
		}
		//validate address
		try {
			$addressValidator = new AddressValidator($data);
			$addressValidator->validateCreate();
		} catch (ValidationException $ve) {
			if ($applicantValidationException) {
				$merged = $applicantValidationException->merge($ve);
				throw $merged;
			}
			throw ($ve);
		}
		if ($applicantValidationException) {
			throw $applicantValidationException;
		}

		$applicant = $this->applicantRepository->create($data);

		//ActivityLogger::log('Betrieb [' . $data['id'] . '] erstellt');
		$this->createApplicantUser($applicant);

		return $applicant;
	}

	public function importApplicants(UploadedFile $file) {
		//validate file (mime types)
		$fileValidator = new ApplicantImportValidator($file);
		$fileValidator->validate();

		//iterate over all entries and try to store them
		//if exceptions occur, all db actions are rolled back to prevent data 
		//inconsistency
		$doc = PHPExcel_IOFactory::load($file->getRealPath());
		$sheet = $doc->getActiveSheet();

		$rowCount = 0;
		DB::beginTransaction();
		try {
			foreach ($sheet->toArray() as $row) {
				//ignore null rows
				$empty = true;
				for ($i = 0; $i <= 14; $i++) {
					if ($row[$i] != null && $row[$i] != '') {
						$empty = false;
					}
				}
				if ($empty) {
					continue;
				}

				$rowCount++;
				$data = array(
					'id' => $row[0],
					'label' => $row[1],
					'title' => $row[2],
					'firstname' => $row[3],
					'lastname' => $row[4],
					'street' => $row[5],
					'zipcode' => $row[6],
					'city' => $row[7],
					'phone' => $row[8],
					'fax' => $row[9],
					'mobile' => $row[10],
					'email' => $row[11],
					'web' => $row[12],
					'association_id' => $row[13],
				);

				//unset email if empty string
				if ($data['email'] === '') {
					unset($data['email']);
				}
				$this->create($data);
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
		//ActivityLogger::log($rowCount . ' Betriebe importiert');
		//return number of read lines
		return $rowCount;
	}

	public function updateApplicant(Applicant $applicant, array $data) {
		$applicantValidationException = null;

		//validate applicant
		try {
			$applicantValidator = new ApplicantValidator($data, $applicant);
			$applicantValidator->validateUpdate();
		} catch (ValidationException $ve) {
			$applicantValidationException = $ve;
		}
		//validate address
		try {
			$addressValidator = new AddressValidator($data, $applicant->address);
			$addressValidator->validateUpdate();
		} catch (ValidationException $ve) {
			if ($applicantValidationException) {
				$merged = $applicantValidationException->merge($ve);
				throw $merged;
			}
			throw ($ve);
		}
		if ($applicantValidationException) {
			throw $applicantValidationException;
		}

		$this->applicantRepository->update($applicant, $data);
		//ActivityLogger::log('Betrieb [' . $applicant->id . '] bearbeitet');
		return $applicant;
	}

	public function getAssociations(User $user = null) {
		if (is_null($user) || $user->admin) {
			return $this->associationRepository->findAll();
		}
		return $this->associationRepository->findForUser($user);
	}

	public function createAssociation(array $data) {
		$associationValidator = new AssociationValidator($data);
		$associationValidator->validateCreate();
		$association = $this->associationRepository->create($data);
		//ActivityLogger::log('Verein [' . $data['name'] . '] erstellt');
		return $association;
	}

	public function updateAssociation(Association $association, array $data) {
		$associationValidator = new AssociationValidator($data, $association);
		$associationValidator->validateUpdate();

		$this->associationRepository->update($association, $data);
		//ActivityLogger::log('Verein [' . $association->name . '] bearbeitet');
		return $association;
	}

	public function deleteAssociation(Association $association) {
		throw new NotImplementedException();
	}

	public function getCompetitions(User $user = null) {
		return $this->competitionRepository->findAll();
	}

	public function resetCompetition(Competition $competition) {
		// TODO: refactor to non-static
		DB::transaction(function() use ($competition) {
			$competition->tastingsessions()->chunk(100,
				function($sessions) {
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
			$competition->wines()->chunk(100,
				function($wines) {
				foreach ($wines as $wine) {
					$wine->tastingnumbers()->delete();
					$wine->delete();
				}
			});

			//$competition->user()->associate(null);
			$competition->competitionState()->associate(CompetitionState::find(CompetitionState::STATE_ENROLLMENT));
			$competition->save();
		});
		//ActivityLogger::log('Bewerb [' . $competition->label . '] zur&uuml;ckgesetzt');
		//return $this->competitionRepository->reset($competition);
	}

	public function getUsers(User $user = null) {
		if (is_null($user) || $user->admin) {
			return $this->userRepository->findAll();
		}
		// Non-admin users see only their own user
		return new Collection([
			$user
		]);
	}

	public function createUser(array $data) {
		$userValidator = new UserValidator($data);
		$userValidator->validateCreate();
		$user = $this->userRepository->create($data);
		//ActivityLogger::log('Benutzer [' . $data['username'] . '] erstellt');
		return $user;
	}

	public function updateUser(User $user, $data) {
		$userValidator = new UserValidator($data, $user);
		$userValidator->validateUpdate();
		$this->userRepository->update($user, $data);
		//ActivityLogger::log('Benutzer [' . $user->username . '] bearbeitet');
	}

	public function deleteUser(User $user) {
		$this->userRepository->delete($user);
		//ActivityLogger::log('Benutzer [' . $username . '] gel&ouml;scht');
	}

	public function getWineSorts() {
		return $this->wineSortRepository->findAll();
	}

	public function createWineSort(array $data) {
		$validator = new WineSortValidator($data);
		$validator->validateCreate();

		return $this->wineSortRepository->create($data);
	}

	public function updateWineSort(WineSort $wineSort, array $data) {
		$validator = new WineSortValidator($data, $wineSort);
		$validator->validateUpdate();

		$this->wineSortRepository->update($wineSort, $data);
	}

	/**
	 * Create user for applicant it it does not exist
	 * 
	 * @param Applicant $applicant
	 * @return Applicant
	 */
	private function createApplicantUser(Applicant $applicant) {
		//for better security, existing users are not associated with the new applicant
		if (!User::find($applicant->id)) {
			$user = $this->userRepository->create([
				'username' => $applicant->id,
				'password' => str_random(15), //random password for better security
				'admin' => false,
			]);
			$applicant->user()->associate($user);
			$applicant->save();
		}
		//ActivityLogger::log('Benutzer [' . $user->username . '] erstellt (zum Betrieb)');
		return $applicant;
	}

}
