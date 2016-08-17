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
use App\TastingNumber;
use App\User;

class TastingNumberAbilities {

	use CommonAbilities;

	private function isCompetitionAdmin(User $user, Competition $competition) {
		return $competition->administrates($user);
	}

	private function isCompetitionAdminByTastingNumber(User $user, TastingNumber $tastingNumber) {
		return $this->isCompetitionAdmin($user, $tastingNumber->wine->competition);
	}

	private function checkCompetitionState(Competition $competition) {
		return in_array($competition->competitionstate->id, [
			CompetitionState::STATE_ENROLLMENT,
			CompetitionState::STATE_TASTINGNUMBERS1,
			CompetitionState::STATE_TASTINGNUMBERS2
		]);
	}

	private function checkEnrollmentFinished(Competition $competition) {
		return $competition->enrollmentFinished();
	}

	private function checkTranslate(Competition $competition) {
		return in_array($competition->competitionstate->id, [
			CompetitionState::STATE_TASTING1,
			CompetitionState::STATE_TASTING2
		]);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function show(User $user, Competition $competition) {
		return $this->isCompetitionAdmin($user, $competition)
			&& $this->checkCompetitionState($competition)
			&& $this->checkEnrollmentFinished($competition);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function assign(User $user, Competition $competition) {
		return $this->isCompetitionAdmin($user, $competition)
			&& $this->checkCompetitionState($competition)
			&& $this->checkEnrollmentFinished($competition);
	}

	/**
	 * @param User $user
	 * @param TastingNumber $tastingNumber
	 * @return bool
	 */
	public function unassign(User $user, TastingNumber $tastingNumber) {
		return $this->isCompetitionAdmin($user, $tastingNumber->wine->competition)
			&& $this->checkCompetitionState($tastingNumber->wine->competition)
			&& $this->checkEnrollmentFinished($tastingNumber->wine->competition);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function import(User $user, Competition $competition) {
		return $this->isCompetitionAdmin($user, $competition)
			&& $this->checkCompetitionState($competition)
			&& $this->checkEnrollmentFinished($competition);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function translate(User $user, Competition $competition) {
		return $this->checkTranslate($competition)
			&& $this->checkCompetitionState($competition)
			&& $this->checkEnrollmentFinished($competition);
	}

}
