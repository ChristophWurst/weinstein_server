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

namespace App\Database\Repositories;

use App\MasterData\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository {

	/**
	 * @return Collection
	 */
	public function findAll() {
		return User::all();
	}

	/**
	 * @param string $username
	 * @return User|null
	 */
	public function find(string $username)
	{
		return User::find($username);
	}

	/**
	 * @param array $data
	 * @return User
	 */
	public function create(array $data) {
		$user = new User($data);
		$user->save();
		return $user;
	}

	/**
	 * @param User $user
	 * @param array $data
	 * @return User
	 */
	public function update(User $user, array $data) {
		$user->update($data);
		return $user;
	}

	/**
	 * @param User $user
	 */
	public function delete(User $user) {
		$user->delete();
	}

}
