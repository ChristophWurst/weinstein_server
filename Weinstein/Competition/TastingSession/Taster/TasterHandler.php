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

namespace Weinstein\Competition\TastingSession\Taster;

use App\Competition\Tasting\Commission;
use App\Competition\Tasting\Taster;
use App\Competition\Tasting\TastingSession;
use Illuminate\Database\Eloquent\Collection;
use Weinstein\Competition\TastingSession\Taster\TasterDataProvider;

class TasterHandler {

	/**
	 * Data provider
	 * 
	 * @var TasterDataProvider
	 */
	private $dataProvider;

	/**
	 * Construct
	 * 
	 * @param TasterDataProvider $dataProvider
	 */
	public function __construct(TasterDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Create a new taster
	 * 
	 * @param array $data
	 * @param TastingSession $tastingSession
	 * @return Taster
	 */
	public function create(array $data, TastingSession $tastingSession) {
		$validator = new TasterValidator($data);
		$validator->setTastingSession($tastingSession);
		$validator->validateCreate();

		$taster = new Taster($data);
		$commission = Commission::find($data['commission_id']);
		$taster->commission()->associate($commission);
		if ($commission->tasters()->orderBy('nr', 'desc')->first()) {
			//commission has existing tasters
			$taster->nr = $commission->tasters()->orderBy('nr', 'desc')->first()->nr + 1;
		} else {
			$taster->nr = 1;
		}
		$taster->active = true;
		$taster->save();
		return $taster;
	}

	/**
	 * Update the taster
	 * 
	 * @param Taster $taster
	 * @param array $data
	 * @param TastingSession $tastingSession
	 * @return Taster
	 */
	public function update(Taster $taster, array $data, TastingSession $tastingSession) {
		$taster->update($data);
		return $taster;
	}

	/**
	 * Delete the taster
	 * 
	 * @param Taster $taster
	 */
	public function delete(Taster $taster) {
		$taster->delete();
	}

	/**
	 * Get all tasters
	 * 
	 * if valid tasting session is given, only its sessions are returned
	 * 
	 * @param TastingSession $tastingSession
	 * @return Collection
	 */
	public function getAll(TastingSession $tastingSession = null) {
		return $this->dataProvider->getAll($tastingSession);
	}

}
