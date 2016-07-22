<?php

use App\Competition\Competition;
use App\Competition\Tasting\TastingNumber;
use App\Competition\Tasting\TastingStage;
use Illuminate\Database\Eloquent\Collection;

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

namespace Weinstein\Competition\TastingNumber;

class TastingNumberDataProvider {

	/**
	 * Get competitions tasting numbers
	 * 
	 * if no valid competition is given, all tasting numbers are returned
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingStage $tastingStage = null) {
		//competition
		if (is_null($competition)) {
			$query = TastingNumber::getQuery();
		} else {
			$query = $competition->tastingnumbers();
		}
		//tasting stage
		if (!is_null($tastingStage)) {
			$query = $query->where('tastingstage_id', '=', $tastingStage->id);
		}
		return $query->orderBy('nr', 'asc')->get();
	}

	/**
	 * Get untasted tasting numbers of given competition
	 * 
	 * @param Competition $competition
	 * @param int $limit
	 * @return Collection
	 */
	public function getUntasted(Competition $competition = null, TastingStage $tastingstage = null, $limit = null) {
		$query = $competition->tastingnumbers()->whereNotIn('tastingnumber.id', function($query) {
				$query->select('tastingnumber_id as id')
				->from('tasting');
			})
			->where('tastingstage_id', '=', $tastingstage->id)
			->orderBy('nr');

		if (is_null($limit)) {
			return $query->get();
		} else {
			return $query->take(2)->get();
		}
	}

}
