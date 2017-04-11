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
use Illuminate\Contracts\Pagination\Paginator;

class WineRepository {

	/**
	 * @param Competition $competition
	 * @param integer $nr
	 * @return Wine|null
	 */
	public function findByNr(Competition $competition, int $nr) {
		return Wine::where('competition_id', $competition->id)
				->where('nr', $nr)
				->first();
	}

	/**
	 * @param Wine $wine
	 * @param string $comment
	 * @return Wine
	 */
	public function addComment(Wine $wine, string $comment): Wine {
		$wine->comment = $comment;
		$wine->save();
		return $wine;
	}

	/**
	 * Get competitions wines
	 * 
	 * @param Competition $competition
	 * @return Paginator
	 */
	public function findAll(Competition $competition) {
		return $competition->wine_details()
				->with('applicant', 'applicant.association', 'winesort', 'winequality')
				->paginate(200);
	}

	/**
	 * Get users wines of given competition
	 * 
	 * @param User $user
	 * @param Competition $competition
	 * @return Paginator
	 */
	public function findUsersWines(User $user, Competition $competition) {
		return $competition->wine_details()
				->where('applicant_username', $user->username)
				->orWhere('association_username', $user->username)
				->with('applicant', 'applicant.association', 'winesort', 'winequality')
				->paginate(200);
	}

	/**
	 * @param Wine $wine
	 * @param array $data
	 * @return Wine
	 */
	public function update(Wine $wine, array $data): Wine {
		$wine->update($data);
		return $wine;
	}

	/**
	 * @param Wine $wine
	 * @return void
	 */
	public function delete(Wine $wine) {
		$wine->delete();
	}

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function resetCatalogueNumbers(Competition $competition) {
		$competition->wines()->update([
			'catalogue_number' => null,
		]);
	}

	/**
	 * @param Competition $competition
	 * @return int
	 */
	public function getNumberOfWinesWithoutCatalogueNumber(Competition $competition): int {
		return $competition->wines()
				->where('chosen', true)
				->where('catalogue_number', null)
				->count();
	}

}
