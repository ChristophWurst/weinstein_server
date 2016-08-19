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

use App\MasterData\Applicant;
use App\MasterData\User;

class ApplicantDataProvider {

	/**
	 * Get all applicants
	 * 
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getAllApplicants() {
		return Applicant::all();
	}

	/**
	 * Get given users administrated applicants
	 * 
	 * @param App\MasterData\User $user
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getApplicantsForUser(User $user) {
		//direct
		$applicants = $user->applicants()->get();

		//indirect
		foreach ($user->associations()->get() as $association) {
			$applicants = $applicants->merge($association->applicants()->get());
		}

		$applicants->sortBy('id');
		return $applicants;
	}

}
