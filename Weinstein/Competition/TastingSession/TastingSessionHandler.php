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

use App\MasterData\Competition;
use App\Tasting\Commission;
use App\Tasting\Taster;
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;
use App\MasterData\User;
use Illuminate\Database\Eloquent\Collection;

class TastingSessionHandler {

	/**
	 * Data provider
	 */
	private $dataProvider;

	/**
	 * Create nr commissions for the given tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @param type $nr
	 */
	private function createCommissions(TastingSession $tastingSession, $nr) {
		$commissionA = new Commission();
		$commissionA->side = 'a';
		$commissionA->tastingsession_id = $tastingSession->id;
		$commissionA->save();

		if ($nr == 2) {
			$commissionB = new Commission();
			$commissionB->side = 'b';
			$commissionB->tastingsession_id = $tastingSession->id;
			$commissionB->save();
		}
	}

	/**
	 * Construct
	 * 
	 * @param TastingSessionDataProvider $dataProvider
	 */
	public function __construct(TastingSessionDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Create new tastingsession
	 * 
	 * @param array $data
	 * @param Competition $competition
	 * @return TastingSession
	 */
	public function create(array $data, Competition $competition) {
		$validator = new TastingSessionValidator($data);
		$validator->setCompetition($competition);
		$validator->validateCreate();

		$tastingSession = new TastingSession($data);
		$tastingSession->nr = $competition->tastingsessions()->ofTastingStage($competition->getTastingStage())->max('nr') + 1;
		$tastingSession->competition()->associate($competition);
		$tastingSession->tastingstage()->associate($competition->getTastingStage());
		$tastingSession->save();

		$this->createCommissions($tastingSession, $data['commissions']);

		return $tastingSession;
	}

	/**
	 * Update the tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @param array $data
	 * @param Competition $competition
	 * @return TastingSession
	 */
	public function update(TastingSession $tastingSession, array $data, Competition $competition) {
		$validator = new TastingSessionValidator($data, $tastingSession);
		$validator->setCompetition($competition);
		$validator->validateUpdate();

		if (!isset($data['wuser_username'])) {
			$tastingSession->wuser_username = null;
		} else {
			$tastingSession->wuser_username = $data['wuser_username'];
		}
		$tastingSession->save();

		return $tastingSession;
	}

	/**
	 * Complete/Lock tasting session
	 * 
	 * @param TastingSession $tastingSession
	 * @return TastingSession
	 */
	public function lock(TastingSession $tastingSession) {
		$tastingSession->locked = true;
		$tastingSession->save();
		return $tastingSession;
	}

	/**
	 * Delete the tasting sessions
	 * 
	 * + delete all tasters
	 * 
	 * @param TastingSession $tastingSession
	 */
	public function delete(TastingSession $tastingSession) {
		//first, delte commission
		$tastingSession->commissions()->delete();
		//second, delete tasting sessin itself
		$tastingSession->delete();
	}

	/**
	 * Add a new taster
	 * 
	 * @param TastingSession
	 * @param array $data
	 * @return Taster
	 */
	public function addTaster(array $data, TastingSession $tastingSession) {
		$taster = \TasterHandler::create($data, $tastingSession);
		$taster->active = true;
		$taster->save();
		return $taster;
	}

	/**
	 * Get all tasting sessions
	 * 
	 * if a valid user is given, only his administrated tasting sessions are returned
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @param User $user
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingStage $tastingStage = null, User $user = null) {
		if (!is_null($user) && $user->admin) {
			return $this->dataProvider->getAll($competition, $tastingStage);
		} else {
			return $this->dataProvider->getAll($competition, $tastingStage, $user);
		}
	}

	/**
	 * Get tasting sessions tasters
	 * 
	 * @param TastingSession $tastingSession
	 */
	public function getTasters(TastingSession $tastingSession) {
		return \TasterHandler::getAll($tastingSession);
	}

}
