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

use App\Commission;
use App\MasterData\CompetitionState;
use App\TastingNumber;
use App\TastingSession;
use App\MasterData\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class TastingAbilities {

	use CommonAbilities;

	private function isTastingSessionAdmin(User $user, TastingSession $tastingSession) {
		return $tastingSession->administrates($user);
	}

	private function isTastingSessionLocked(TastingSession $tastingSession) {
		return $tastingSession->locked;
	}

	private function checkTastingSessionState(TastingSession $tastingSession) {
		$competition = $tastingSession->competition;

		if (!in_array($competition->competitionstate->id, [CompetitionState::STATE_TASTING1, CompetitionState::STATE_TASTING2])) {
			Log::info('competition states do not match');
			return false;
		}
		if ($tastingSession->tastingstage->id !== $competition->getTastingStage()->id) {
			Log::info('tasting stages do not match');
			return false;
		}
		return true;
	}

	/**
	 * Make sure retastings are only done by commissions that belong to the 
	 * same competition as the tasting session does
	 *
	 * @todo check condition below
	 *
	 * @param TastingSession $tastingSession
	 * @param Commission $commission
	 */
	private function competitionsMatch(TastingSession $tastingSession, Commission $commission, TastingNumber $tastingNumber) {
		$competition1 = $tastingSession->competition;
		$competition2 = $commission->tastingsession->competition;

		if ($competition1->id !== $competition2->id) {
			return false;
		}

		$competition3 = $tastingNumber->wine->competition;

		//same competition?
		if ($competition1->id !== $competition3->id) {
			Log::info('competitions do not match');
			return false;
		}

		//same tasting stage?
		$tastingStage1 = $tastingNumber->tastingstage;
		$tastingStage2 = $competition1->getTastingStage();

		if ($tastingStage1->id !== $tastingStage3->id) {
			Log::info('tasting stages do not match');
			return false;
		}
		return true;
	}

	public function create(User $user, TastingSession $tastingSession) {
		return $this->isTastingSessionAdmin($user, $tastingSession) && !$this->isTastingSessionLocked($tastingSession) && $this->checkTastingSessionState($tastingSession);
	}

	public function edit(User $user, TastingSession $tastingSession, Commission $commission, TastingNumber $tastingNumber) {
		return $this->isTastingSessionAdmin($user, $tastingSession) && !$this->isTastingSessionLocked($tastingSession) && $this->competitionsMatch($tastingSession, $commission, $tastingNumber) && $this->checkTastingSessionState($tastingSession);
	}

}
