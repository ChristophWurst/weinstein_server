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

use App\MasterData\Applicant;
use App\MasterData\User;

class ApplicantAbilities {

	use CommonAbilities;

	private function administrates(User $user, Applicant $applicant) {
		if ($applicant->wuser_username === $user->username) {
			return true;
		}
		if (!is_null($applicant->association) && $applicant->association->wuser_username === $user->username) {
			return true;
		}
		return false;
	}

	public function show(User $user, Applicant $applicant) {
		return $this->administrates($user, $applicant);
	}

	public function create(User $user) {
		return $user->associations()->exists();
	}

	public function import(User $user) {
		return false;
	}

	public function edit(User $user, Applicant $applicant) {
		return $this->administrates($user, $applicant);
	}

}
