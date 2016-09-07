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

	private function checkAddWine(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_ENROLLMENT;
	}

	private function checkEditWine(User $user, Wine $wine) {
		$competition = $wine->competition;

		if (!$user->isAdmin() && !is_null($wine->nr)) {
			// Once ID is set, only admin may edit the wine
			return false;
		}

		if ($competition->competitionState->id !== CompetitionState::where('description', '=', 'ENROLLMENT')->first()->id) {
			return false;
		}
		return true;
	}

	private function checkImportKdb(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_KDB;
	}

	private function checkImportExcluded(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_EXCLUDE;
	}

	private function checkEditSosi(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_SOSI;
	}

	private function checkSosiImport(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_SOSI;
	}

	private function isWineAdmin(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	private function checkEditChosen(User $user, Wine $wine, Competition $competition) {
		return !$wine->applicant->association->administrates($user) && $competition->competitionState->id === CompetitionState::STATE_CHOOSE;
	}

	private function checkImportChosen(Competition $competition) {
		return $competition->competitionState->id === CompetitionState::STATE_CHOOSE;
	}

	private function checkExportFlaws(Competition $competition) {
		return $competition->competitionState->id >= CompetitionState::STATE_KDB;
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function show(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return boolean|null
	 */
	public function enrollmentPdf(User $user, Competition $competition) {
		
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
	 * @param Wine $wine
	 * @return boolean|null
	 */
	public function updateExcluded(User $user, Wine $wine) {
		
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
	 * @param Wine $wine
	 * @return boolean|null
	 */
	public function importSosi(User $user, Wine $wine) {
		
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
