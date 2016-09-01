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
use App\TastingStage;
use App\MasterData\User;
use Illuminate\Support\Facades\Log;

class CompetitionAbilities {

	use CommonAbilities;

	public function show(User $user, Competition $competition) {
		return true; // TODO: ?
	}

	public function reset(User $user, Competition $competition) {
		return false;
	}

	public function completeTasingNumbers(User $user, Competition $competition) {
		// TODO: what about the competition admin???
		if (!$user->isAdmin()) {
			return false;
		}

		if ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS1) {
			$withNumber = $competition->wines()->withTastingNumber(TastingStage::find(1))->count();
			$total = $competition->wine_details()->count();
			if ($withNumber < $total) {
				return false;
			}
			return true;
		} else if ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS2) {
			// just allow it - there are no restrictions (for now)
			return true;
		} else {
			Log::error('invalid competition state in complete-tastingnumbers-filter');
			return false;
		}
	}

	public function completeTasting(User $user, Competition $competition) {
		if (!$user->isAdmin()) {
			return false;
		}

		if ($competition->competitionState->id === CompetitionState::STATE_TASTING1) {
			$tasted = $competition->wine_details()->whereNotNull('rating1')->count();
			$total = $competition->wine_details()->count();
			if ($tasted < $total) {
				return false;
			}
			return true;
		} else if ($competition->competitionState->id === CompetitionState::STATE_TASTING2) {
			// just allow it - there are no restrictions (for now)
			return true;
		} else {
			Log::error('invalid competition state in complete-tasting-filter');
			return false;
		}
	}

	public function completeKdb(User $user, Competition $competition) {
		return $user->isAdmin() && $competition->competitionState->id === CompetitionState::STATE_KDB;
	}

	public function completeExcluded(User $user, Competition $competition) {
		return $user->isAdmin() && $competition->competitionState->id === CompetitionState::STATE_EXCLUDE;
	}

	public function completeSosi(User $user, Competition $competition) {
		return $user->isAdmin() && $competition->competitionState->id === CompetitionState::STATE_SOSI;
	}

	public function completeChoosing(User $user, Competition $competition) {
		return $user->isAdmin() && $competition->competitionState->id === CompetitionState::STATE_CHOOSE;
	}

}
