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
use App\Database\Repositories\UserRepository;
use App\Exceptions\NotImplementedException;
use Illuminate\Support\Collection;

class Store implements MasterDataStore {

	/** @var UserRepository */
	private $userRepository;

	public function __construct(UserRepository $userRepository) {
		$this->userRepository = $userRepository;
	}

	public function getApplicants() {
		throw new NotImplementedException();
	}

	public function getAssociations() {
		throw new NotImplementedException();
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
