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
use App\Tasting\TastingSession;

/**
 * @todo review permissions, they are probably wrong
 */
class TastingSessionAbilities {

	use CommonAbilities;

	private function isTastinSessionAdmin(User $user, TastingSession $tastingSession) {
		return $tastingSession->administrates($user);
	}

	private function checkTastingStage(Competition $competition) {
		return in_array($competition->competitionState->id, [
			CompetitionState::STATE_TASTING1,
			CompetitionState::STATE_TASTING2
		]);
	}

	private function checkTastingSessionLocked(TastingSession $tastingSession) {
		return $tastingSession->locked;
	}

	private function isTastingSessionDeletable(TastingSession $tastingSession) {
		return $tastingSession->tasters->count() > 0;
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function showAll(User $user, Competition $competition) {
		// TODO: only admins??
		return $this->checkTastingStage($competition);
	}

	/**
	 * @param User $user
	 * @param Competition $competition
	 * @return bool
	 */
	public function create(User $user, Competition $competition) {
		return $this->checkTastingStage($competition);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function edit(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function show(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function tasters(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function addTaster(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& !$this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function showStatistics(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function exportResult(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function lock(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession);
	}

	/**
	 * @param User $user
	 * @param TastingSession $tastingSession
	 * @return bool
	 */
	public function delete(User $user, TastingSession $tastingSession) {
		return $this->isTastinSessionAdmin($user, $tastingSession)
			&& $this->checkTastingStage($tastingSession->competition)
			&& $this->checkTastingSessionLocked($tastingSession)
			&& $this->isTastingSessionDeletable($tastingSession);
	}

}
