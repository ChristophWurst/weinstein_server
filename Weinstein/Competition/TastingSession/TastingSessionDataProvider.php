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

namespace Weinstein\Competition\TastingSession;

use App\Competition\Competition;
use App\Competition\Tasting\TastingSession;
use App\Competition\Tasting\TastingStage;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class TastingSessionDataProvider {

	/**
	 * Get all tasting sessions
	 * 
	 * if a valid user is given, only his administrated sessions are returned
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @param User $user
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingStage $tastingStage, User $user = null) {
		//competition
		if (is_null($competition)) {
			$query = TastingSession::getQuery();
		} else {
			$query = $competition->tastingsessions();
		}
		//tasting stage
		if (!is_null($tastingStage)) {
			$query = $query->where('tastingstage_id', '=', $tastingStage->id);
		}
		//use
		if (!is_null($user)) {
			$query = $query->where('wuser_username', '=', $user->username);
		}
		return $query->orderBy('nr', 'asc')->get();
	}

}
