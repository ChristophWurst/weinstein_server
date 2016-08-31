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
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;

class TastingSessionRepository {

	public function findAll($competition, $tastingStage) {
		$query = $competition->tastingsessions();
		$query = $query->where('tastingstage_id', '=', $tastingStage->id);
		return $query->orderBy('nr', 'asc')->get();
	}

	public function findForUser($competition, $tastingStage, $user) {
		$query = $competition->tastingsessions();
		$query = $query->where('tastingstage_id', '=', $tastingStage->id);
		$query = $query->where('wuser_username', '=', $user->username);
		return $query->orderBy('nr', 'asc')->get();
	}

	public function create(array $data, Competition $competition, TastingStage $tastingStage) {
		$tastingSession = new TastingSession($data);
		$tastingSession->competition()->associate($competition);
		$tastingSession->tastingstage()->associate($tastingStage);
		$tastingSession->save();
		return $tastingSession;
	}

	public function update(TastingSession $tastingSession, array $data) {
		$tastingSession->update($data);
	}

	public function delete(TastingSession $tastingSession) {
		//first, delte commission
		$tastingSession->commissions()->delete();
		//second, delete tasting session itself
		$tastingSession->delete();
	}

}
