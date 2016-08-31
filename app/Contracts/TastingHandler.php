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

namespace App\Contracts;

use App\MasterData\Competition;
use App\MasterData\User;
use App\Tasting\Commission;
use App\Tasting\Taster;
use App\Tasting\Tasting;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface TastingHandler {

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockTastingNumbers(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockTasting(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockKdb(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockExcluded(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockSosi(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return void
	 */
	public function lockChoosing(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return boolean
	 */
	public function isTastingFinished(Competition $competition);

	/**
	 * @param array $data
	 * @return TastingNumber
	 */
	public function createTastingNumber(array $data, Competition $competition);

	/**
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return integer
	 */
	public function importTastingNumbers(UploadedFile $file, Competition $competition);

	/**
	 * @param TastingNumber $tastingNumber
	 * @return void
	 */
	public function deleteTastingNumber(TastingNumber $tastingNumber);

	/**
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @return Collection
	 */
	public function getUntastedTastingNumbers(Competition $competition, TastingStage $tastingStage);

	/**
	 * Get competitions tasting numbers
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @return Collection
	 */
	public function getAllTastingNumbers(Competition $competition, TastingStage $tastingStage);

	/**
	 * Get all tasting sessions
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @param User $user
	 * @return Collection
	 */
	public function getAllTastingSessions(Competition $competition, TastingStage $tastingStage, User $user = null);

	/**
	 * @param array $data
	 * @param Competition $competition
	 * @return TastingSession
	 */
	public function createTastingSession(array $data, Competition $competition);

	/**
	 * @param TastingSession $tastingSession
	 * @param array $data
	 * @return void
	 */
	public function updateTastingSession(TastingSession $tastingSession, array $data);

	/**
	 * @param TastingSession $tastingSession
	 * @return void
	 */
	public function lockTastingSession(TastingSession $tastingSession);

	/**
	 * @param TastingSession $tastingSession
	 * @return void
	 */
	public function deleteTastingSession(TastingSession $tastingSession);

	/**
	 * Add a new taster
	 * 
	 * @param array $data
	 * @param TastingSession $tastingSession
	 * @return Taster
	 */
	public function addTasterToTastingSession(array $data, TastingSession $tastingSession);

	/**
	 * @param TastingSession $tastingSession
	 * @return Collection
	 */
	public function getTastingSessionTasters(TastingSession $tastingSession);

	/**
	 * @param array $data
	 * @param TastingSession $tastingSession
	 * @return Tasting
	 */
	public function createTasting(array $data, TastingSession $tastingSession);

	/**
	 * @param array $data
	 * @param TastingSession $tastingSession
	 * @param Commission $commission
	 * @return void
	 */
	public function updateTasting(array $data, TastingNumber $tastingNumber, TastingSession $tastingSession, Commission $commission);

	/**
	 * @param TastingSession $tastingSession
	 * @return array
	 */
	public function getNextTastingNumbers(TastingSession $tastingSession);

	/**
	 * @param TastingNumber $tastingNumber
	 * @return bool
	 */
	public function isTastingNumberTasted(TastingNumber $tastingNumber);
}
