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

namespace App\Database\Repositories;

use App\MasterData\Competition;
use App\Tasting\TastedWine;
use App\Tasting\TastingNumber;
use App\Tasting\TastingStage;
use App\Wine;
use Illuminate\Support\Collection;

class TastingNumberRepository {

	public function findAll() {
		return TastingNumber::all();
	}

	public function find($id) {
		return TastingNumber::find($id);
	}

	public function findAllForCompetitionTastingStage(Competition $competition, TastingStage $tastingStage) {
		$query = $competition->tastingnumbers();
		$query = $query->where('tastingstage_id', '=', $tastingStage->id);
		return $query->orderBy('nr', 'asc')->get();
	}

	/**
	 * Get untasted tasting numbers of given competition
	 * 
	 * @param Competition $competition
	 * @param int $limit
	 * @return Collection
	 */
	public function findUntasted(Competition $competition, TastingStage $tastingStage, $limit = 2) {
		$query = $competition->tastingnumbers()->whereNotIn('tastingnumber.id',
				function($query) {
				$query->select('tastingnumber_id as id')->from('tasting');
			})
			->where('tastingstage_id', '=', $tastingStage->id)
			->orderBy('nr');

		return $query->take($limit)->get();
	}

	public function create(array $data, Wine $wine, TastingStage $tastingStage) {
		$tastingNumber = new TastingNumber($data);

		$tastingNumber->wine()->associate($wine);
		$tastingNumber->tastingstage()->associate($tastingStage);
		$tastingNumber->save();

		return $tastingNumber;
	}

	public function delete(TastingNumber $tastingNumber) {
		$tastingNumber->delete();
	}

	public function isTasted(TastingNumber $tastingNumber) {
		return TastedWine::where('tastingnumber_id', $tastingNumber->id)->count() > 0;
	}

}
