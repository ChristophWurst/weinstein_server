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

use App\Tasting\Taster;
use App\Tasting\Tasting;
use App\Tasting\TastingNumber;

class TastingRepository
{
    /**
     * @param array $data
     * @param Taster $taster
     * @param TastingNumber $tastingNumber
     * @return Tasting
     */
    public function create(array $data, Taster $taster, TastingNumber $tastingNumber)
    {
        $tasting = new Tasting($data);
        $tasting->taster()->associate($taster);
        $tasting->tastingnumber()->associate($tastingNumber);
        $tasting->save();

        return $tasting;
    }

    public function clear(TastingNumber $tastingNumber)
    {
        $tastingNumber->tastings()->delete();
    }
}
