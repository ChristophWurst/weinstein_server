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

namespace Weinstein\Association;

use App\MasterData\Association;
use App\MasterData\User;

class AssociationDataProvider {

	/**
	 * Get all associations
	 * 
	 * if a valid $user is given, only its administrated associations are returned
	 * 
	 * @param User $user
	 * @return type
	 */
	public function getAll(User $user = null) {
		if (is_null($user)) {
			$query = Association::query();
		} else {
			$query = $user->associations();
		}
		return $query->orderBy('id')->get();
	}

}
