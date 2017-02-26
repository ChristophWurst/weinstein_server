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

use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Wine;

class WineAbilities {

	use CommonAbilities;

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	public function show(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean
	 */
	public function create(User $user, Competition $competition) {
		return $competition->competitionState->is(CompetitionState::STATE_ENROLLMENT);
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @return boolean
	 */
	private function updatesKdb(Wine $wine, array $data) {
		if (!isset($data['kdb'])) {
			return false;
		}
		return $wine->kdb !== $data['kdb'];
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @return boolean
	 */
	private function updatesSosi(Wine $wine, array $data) {
		if (!isset($data['sosi'])) {
			return false;
		}
		return $wine->sosi !== $data['sosi'];
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @return boolean
	 */
	private function updatesChosen(Wine $wine, array $data) {
		if (!isset($data['chosen'])) {
			return false;
		}
		return $wine->chosen !== $data['chosen'];
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @return boolean
	 */
	private function updatesExcluded(Wine $wine, array $data) {
		if (!isset($data['excluded'])) {
			return false;
		}
		return $wine->excluded !== $data['excluded'];
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	private function mayUpdateKdb(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	private function mayUpdateSosi(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	/**
	 * Only competition admins and association admins may
	 * perform a 'chosen' state change
	 *
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	private function mayUpdateChosen(User $user, Wine $wine) {
		return $wine->competition->administrates($user) || $wine->applicant->association->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	private function mayUpdateExcluded(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	public function update(User $user, Wine $wine, array $data) {
		if ($this->updatesKdb($wine, $data) && !$this->mayUpdateKdb($user, $wine)) {
			return false;
		}
		if ($this->updatesSosi($wine, $data) && !$this->mayUpdateSosi($user, $wine)) {
			return false;
		}
		if ($this->updatesChosen($wine, $data) && !$this->mayUpdateChosen($user, $wine)) {
			return false;
		}
		if ($this->updatesExcluded($wine, $data) && !$this->mayUpdateExcluded($user, $wine)) {
			return false;
		}
		return true;
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean
	 */
	public function enrollmentPdf(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function edit(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function delete(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function redirect(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function kdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean|null
	 */
	public function updateKdb(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function importKdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function importExcluded(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean|null
	 */
	public function updateSosi(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function importSosi(User $user, Competition $competition) {
		return $competition->administrates($user);
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return boolean|null
	 */
	public function updateChosen(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function importChosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function excluded(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function sosi(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function chosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function exportAll(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function exportKdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function exportSosi(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function exportChosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function exportFlaws(User $user, Competition $competition) {
		
	}

}
