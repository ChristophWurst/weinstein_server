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

interface TastingHandler {

	/**
	 * @param Competition $competition
	 * @param int $tasting
	 */
	public function lockTastingNumbers(Competition $competition, $tasting);

	/**
	 * @param Competition $competition
	 * @param int $tasting
	 */
	public function lockTasting(Competition $competition, $tasting);

	/**
	 * @param Competition $competition
	 */
	public function lockKdb(Competition $competition);

	/**
	 * @param Competition $competition
	 */
	public function lockExcluded(Competition $competition);

	/**
	 * @param Competition $competition
	 */
	public function lockSosi(Competition $competition);

	/**
	 * @param Competition $competition
	 */
	public function lockChoosing(Competition $competition);

	/**
	 * @param Competition $competition
	 * @return boolean
	 */
	public function isTastingFinished(Competition $competition);
}
