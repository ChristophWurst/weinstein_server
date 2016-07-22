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

namespace Weinstein\Competition\Wine;

use App\User;
use Applicant;
use Association;
use Competition;
use Illuminate\Database\Eloquent\Collection;
use WineDetails;

class WineDataProvider {

	/**
	 * Get applicants wines for given competition
	 * 
	 * @param Applicant $applicant
	 * @return Collection
	 */
	private function getApplicantWines(Applicant $applicant, Competition $competition) {
		return $competition->wines()->where('applicant_id', '=', $applicant->id)->get();
	}

	/**
	 * Get associations wines for given competition
	 * - directly associated wines
	 * - indirectly (through its applicants) associated wines
	 * 
	 * @param Association $association
	 * @return Collection
	 */
	private function getAssociationWines(Association $association, Competition $competition) {
		//direct wines
		$wines = $competition->wines()->where('association_id', '=', $association->id)->get();

		//applicants wines
		foreach ($association->applicants()->get() as $applicant) {
			$wines = $wines->merge($this->getApplicantWines($applicant, $competition));
		}

		return $wines;
	}

	/**
	 * Get competitions wines
	 * 
	 * @param Competition $competition
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, $queryOnly = false) {
		if (is_null($competition)) {
			$query = WineDetails::getQuery();
		} else {
			$query = $competition->wine_details();
		}
		if ($queryOnly) {
			return $query;
		} else {
			return $query->get();
		}
	}

	/**
	 * Get users wines of given competition
	 * 
	 * @param User $user
	 * @param Competition $competition
	 * @praam boolen $queryOnly
	 * @return Collection2
	 */
	public function getUsersWines(User $user, Competition $competition, $queryOnly = false) {
		$query = WineDetails::where('applicant_username', $user->username)
			->orWhere('association_username', $user->username)
			->orderBy('nr');
		if ($queryOnly) {
			return $query;
		} else {
			return $query->get();
		}
	}

}
