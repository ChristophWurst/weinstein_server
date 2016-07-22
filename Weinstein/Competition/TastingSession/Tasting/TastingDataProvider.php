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

namespace Weinstein\Competition\TastingSession\Tasting;

use App\Competition\Competition;
use App\Competition\Tasting\Tasting;
use App\Competition\Tasting\TastingSession;
use Illuminate\Database\Eloquent\Collection;

class TastingDataProvider {

	/**
	 * Get all tastings
	 * 
	 * @param Competition $competition
	 * @param TastingSession $tastingSession
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingSession $tastingSession = null) {
		//competition
		if (is_null($competition)) {
			$query = Tasting::getQuery();
		}
		//tastingsession
		if (!is_null($tastingSession)) {
			$query = $query->where('tastingsession_id', '=', $tastingSession->id);
		}
		return $query->orderBy('id')->get();
	}

}
