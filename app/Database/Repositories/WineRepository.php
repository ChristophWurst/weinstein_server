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
use App\MasterData\User;
use App\Wine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder;

class WineRepository {

	public function findByNr(Competition $competition, $nr) {
		return Wine::where('competition_id', $competition->id)
			->where('nr', $nr)
			->first();
	}

	public function addComment(Wine $wine, $comment) {
		$wine->comment = $comment;
		$wine->save();
	}

	/**
	 * Get competitions wines
	 * 
	 * @param Competition $competition
	 * @param boolean $queryOnly
	 * @return Collection|Relation
	 */
	public function findAll(Competition $competition, $queryOnly = false) {
		$query = $competition->wine_details();
		if ($queryOnly) {
			return $query;
		} else {
			return $query->get();
		}
	}

	/**
	 * Get users wines of given competition
	 * 
	 * @param User $user
	 * @param Competition $competition
	 * @param boolean $queryOnly
	 * @return Collection|Builder
	 */
	public function findUsersWines(User $user, Competition $competition, $queryOnly = false) {
		$query = $competition->wine_details()
			->where('applicant_username', $user->username)
			->orWhere('association_username', $user->username)
			->orderBy('nr');
		if ($queryOnly) {
			return $query;
		} else {
			return $query->get();
		}
	}

	public function update(Wine $wine, array $data) {
		$wine->update($data);
	}

	public function delete(Wine $wine) {
		$wine->delete();
	}

}
