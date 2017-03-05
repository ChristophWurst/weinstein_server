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

use App\MasterData\WineSort;
use Illuminate\Database\Eloquent\Collection;

class WineSortRepository {

	/**
	 * @return Collection
	 */
	public function findAll() {
		return WineSort::all();
	}

	/**
	 * @param array $data
	 * @return WineSort
	 */
	public function create(array $data) {
		return WineSort::create($data);
	}

	/**
	 * @param WineSort $wineSort
	 * @param array $data
	 */
	public function update(WineSort $wineSort, array $data) {
		$wineSort->update($data);
	}

}
