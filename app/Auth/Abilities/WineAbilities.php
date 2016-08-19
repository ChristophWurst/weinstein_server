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

use App\Competition;
use App\CompetitionState;
use App\MasterData\User;
use App\Wine;

class WineAbilities {

	use CommonAbilities;

	private function checkAddWine(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_ENROLLMENT;
	}

	private function checkEditWine(User $user, Wine $wine) {
		$competition = $wine->competition;

		if (!$this->isAdmin($user) && !is_null($wine->nr)) {
			// Once ID is set, only admin may edit the wine
			return false;
		}

		if ($competition->competitionstate->id !== CompetitionState::where('description', '=', 'ENROLLMENT')->first()->id) {
			return false;
		}
		return true;
	}

	private function checkImportKdb(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_KDB;
	}

	private function checkImportExcluded(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_EXCLUDE;
	}

	private function checkEditSosi(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_SOSI;
	}

	private function checkSosiImport(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_SOSI;
	}

	private function isWineAdmin(User $user, Wine $wine) {
		return $wine->administrates($user);
	}

	private function checkEditChosen(User $user, Wine $wine, Competition $competition) {
		return !$wine->applicant->association->administrates($user) && $competition->competitionstate->id === CompetitionState::STATE_CHOOSE;
	}

	private function checkImportChosen(Competition $competition) {
		return $competition->competitionstate->id === CompetitionState::STATE_CHOOSE;
	}

	private function checkExportFlaws(Competition $competition) {
		return $competition->competitionstate->id >= CompetitionState::STATE_KDB;
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function showAll(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function show(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function create(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function enrollmentPdf(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function edit(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function delete(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function redirect(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function kdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return bool
	 */
	public function updateKdb(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function importKdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return bool
	 */
	public function updateExcluded(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function importExcluded(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return bool
	 */
	public function updateSosi(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return bool
	 */
	public function importSosi(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Wine $wine
	 * @return bool
	 */
	public function updateExcluded(User $user, Wine $wine) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function importChosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function excluded(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function sosi(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function chosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function exportAll(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function exportKdb(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function exportSosi(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function exportChosen(User $user, Competition $competition) {
		
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function exportFlaws(User $user, Competition $competition) {
		
	}

}
