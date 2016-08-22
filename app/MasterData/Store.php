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
use App\Database\Repositories\AssociationRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\UserRepository;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Collection;

class Store implements MasterDataStore {

	/** @var AssociationRepository */
	private $associationRepository;

	/** @var CompetitionRepository */
	private $competitionRepository;

	/** @var UserRepository */
	private $userRepository;

	public function __construct(AssociationRepository $associationRepository,
		CompetitionRepository $competitionRepository, UserRepository $userRepository) {
		$this->associationRepository = $associationRepository;
		$this->competitionRepository = $competitionRepository;
		$this->userRepository = $userRepository;
	}

	public function getApplicants() {
		throw new NotImplementedException();
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

	public function getUsers(User $user = null) {
		if (is_null($user) || $user->admin) {
			return $this->userRepository->findAll();
		}
		// Non-admin users see only their own user
		return new Collection([
			$user
		]);
	}

	public function createUser($data) {
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

}
