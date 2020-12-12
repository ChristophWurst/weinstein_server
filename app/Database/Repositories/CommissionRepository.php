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
 */

namespace App\Database\Repositories;

use App\Tasting\Commission;
use App\Tasting\TastingSession;

class CommissionRepository
{
    /**
     * @param int $id
     * @return Commission
     */
    public function find($id)
    {
        return Commission::find($id);
    }

    /**
     * @param array $data
     * @param TastingSession $tastingSession
     * @return Commission
     */
    public function create(array $data, TastingSession $tastingSession)
    {
        $commission = new Commission($data);
        $commission->tastingSession()->associate($tastingSession);
        $commission->save();

        return $commission;
    }
}
