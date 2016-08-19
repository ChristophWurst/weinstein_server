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

namespace Weinstein\WineSort;

use App\MasterData\Competition;
use App\MasterData\WineSort;

class WineSortDataProvider {

    /**
     * Get all wine sorts
     * 
     * if valid competition is given, only its wine sorts are returned
     * 
     * @param Competition $competition
     */
    public function getAll(Competition $competition = null) {
        if (is_null($competition)) {
            return WineSort::all();
        } else {
            return $competition->winesorts()->get();
        }
    }

}
