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

use App\Association;
use App\User;

class AssociationAbilities {

	use CommonAbilities;

	private function administrates(User $user, Association $association) {
		return $association->user && $association->user->username !== $user->username;
	}

	public function show(User $user, Association $association) {
		return $this->administrates($user, $association);
	}

	public function create(User $user) {
		return $this->isAdmin($user);
	}

	public function edit(User $user, Association $association) {
		return $this->administrates($user, $association);
	}

}
