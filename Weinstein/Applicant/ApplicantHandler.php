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

namespace Weinstein\Applicant;

use ActivityLogger;
use Address;
use App\Database\Repositories\UserRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Applicant;
use App\MasterData\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use PHPExcel_IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use function str_random;

class ApplicantHandler {

	/**
	 * @var UserRepository
	 */
	private $userRepository;

	/**
	 * Data provider
	 * 
	 * @var ApplicantDataProvider
	 */
	private $dataProvider;

	/**
	 * Create user for applicant it it does not exist
	 * 
	 * @param Applicant $applicant
	 * @return Applicant
	 */
	private function createUser(Applicant $applicant) {
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
		ActivityLogger::log('Benutzer [' . $user->username . '] erstellt (zum Betrieb)');
		return $applicant;
	}

	/**
	 * @param ApplicantDataProvider $dataProvider
	 */
	public function __construct(ApplicantDataProvider $dataProvider, UserRepository $userRepository) {
		$this->dataProvider = $dataProvider;
		$this->userRepository = $userRepository;
	}

	/**
	 * Create a new applicant
	 * - creates a new user for the applicant
	 * 
	 * @param array $data
	 * @return Applicant
	 * @throws ValidationException
	 */
	public function create(array $data) {
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

		$applicant = new Applicant($data);
		$address = new Address($data);
		$address->save();
		$applicant->address()->associate($address);
		$applicant->save();

		ActivityLogger::log('Betrieb [' . $data['id'] . '] erstellt');
		$this->createUser(Applicant::find($data['id']));

		return $applicant;
	}

	/**
	 * Update an existing appliant
	 * 
	 * @param Applicant $applicant
	 * @param array $data
	 * @return Applicant
	 */
	public function update(Applicant $applicant, array $data) {
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

		//only admin may edit user
		if (Auth::user()->admin) {
			if (!isset($data['wuser_username'])) {
				$applicant->wuser_username = null;
				$applicant->save();
			}
		} elseif (isset($data['wuser_username'])) {
			unset($data['wuser_username']);
		}

		$applicant->update($data);
		$applicant->address->update($data);
		ActivityLogger::log('Betrieb [' . $applicant->id . '] bearbeitet');
		return $applicant;
	}

	/**
	 * Delete an applicant
	 * 
	 * @param Applicant $applicant
	 */
	public function delete(Applicant $applicant) {
		$id = $applicant->id;
		$applicant->delete();
		ActivityLogger::log('Betrieb [' . $id . '] gel&ouml;scht');
	}

	/**
	 * Import applicants from an uploaded file
	 * 
	 * @param UploadedFile $file
	 * @return int Number of read lines
	 */
	public function import(UploadedFile $file) {
		//validate file (mime types)
		$fileValidator = new ApplicantImportValidator($file);
		$fileValidator->validate();

		//iterate over all entries and try to store them
		//
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
		ActivityLogger::log($rowCount . ' Betriebe importiert');
		//return number of read lines
		return $rowCount;
	}

	/**
	 * Get users applicants
	 * 
	 * @param \Weinstein\Applicant\User $user
	 * @return Collection
	 */
	public function getUsersApplicants(User $user) {
		if ($user->admin) {
			return $this->dataProvider->getAllApplicants();
		} else {
			return $this->dataProvider->getApplicantsForUser($user);
		}
	}

}
