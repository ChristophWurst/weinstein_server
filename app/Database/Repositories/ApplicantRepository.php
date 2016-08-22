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

use App\Exceptions\NotImplementedException;
use App\MasterData\Address;
use App\MasterData\Applicant;
use App\MasterData\User;

class ApplicantRepository {

	public function findAll() {
		return Applicant::all();
	}

	public function findForUser(User $user) {
		$direct = $user->applicants()->get();
		$indirect = $user->associationApplicants()->get();

		$all = $direct->merge($indirect);
		$all->sortBy('id');
		return $all;
	}

	public function create(array $data) {
		$applicant = new Applicant($data);
		$address = new Address($data);
		$address->save();
		$applicant->address()->associate($address);
		$applicant->save();
		return $applicant;
	}

	public function update(Applicant $applicant, array $data) {
		$applicant->update($data);
		$applicant->address->update($data);
	}

}
