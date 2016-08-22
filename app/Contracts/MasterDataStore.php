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

namespace App\Contracts;

use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\User;
use Illuminate\Support\Collection;

interface MasterDataStore {

	/**
	 * @return Collection
	 */
	public function getAssociations(User $user = null);

	/**
	 * @param array $data
	 * @return Association
	 */
	public function createAssociation(array $data);

	/**
	 * @param Association $association
	 * @param array $data
	 */
	public function updateAssociation(Association $association, array $data);

	/**
	 * @param Association $association
	 */
	public function deleteAssociation(Association $association);

	/**
	 * @param User $user
	 */
	public function getCompetitions(User $user = null);

	/**
	 * @param Competition $competition
	 */
	public function resetCompetition(Competition $competition);
	
	/**
	 * @return Collection
	 */
	public function getUsers(User $user = null);

	/**
	 * @param array $data
	 * @return User
	 */
	public function createUser($data);

	/**
	 * @param User $user
	 * @param array $data
	 */
	public function updateUser(User $user, $data);

	/**
	 * @param User $user
	 */
	public function deleteUser(User $user);

	/**
	 * @return Collection
	 */
	public function getApplicants();
}
