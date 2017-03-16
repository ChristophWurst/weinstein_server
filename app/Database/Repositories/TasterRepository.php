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

use App\Tasting\Commission;
use App\Tasting\Taster;

class TasterRepository {

	public function find($id) {
		return Taster::find($id);
	}

	public function findForCommission(Commission $commission) {
		return $commission->tasters;
	}

	/**
	 * @param Commission $commission
	 */
	public function create($data, Commission $commission) {
		$taster = new Taster($data);
		$taster->commission()->associate($commission);
		$taster->save();
		return $taster;
	}

	public function getActive(Commission $commission) {
		return $commission->tasters()->active()->get();
	}

	public function update(Taster $taster, array $data) {
		$taster->update($data);
	}

}
