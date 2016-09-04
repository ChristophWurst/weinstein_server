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

namespace App\Auth\Abilities;

use App\MasterData\User;

class UserAbilities {

	use CommonAbilities;

	private function administratesUser(User $user1, User $user2) {
		return $user2->administrates($user1);
	}

	/**
	 * @return boolean
	 */
	public function create() {
		return false; // Admin only
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 * @return bool
	 */
	public function show(User $user1, User $user2) {
		return $this->administratesUser($user1, $user2);
	}

	/**
	 * @param User $user1
	 * @param User $user2
	 */
	public function edit(User $user1, User $user2) {
		return $this->administratesUser($user1, $user2);
	}

	/**
	 * @return boolean
	 */
	public function delete() {
		return false; // Admin only
	}

}
