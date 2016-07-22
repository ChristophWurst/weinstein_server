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
class TasterTableSeeder extends Seeder {

    /**
     * Insert new taster into database
     * 
     * @param int $nr
     * @param string $name
     * @param boolean $active
     * @param tasterside $commission
     * @param int $tastingsession
     * @return Taster
     */
    public static function createTaster($nr, $name, $active, $commission) {
        return Taster::create(array(
                    'nr' => $nr,
                    'name' => $name,
                    'active' => $active,
                    'commission_id' => $commission,
        ));
    }

}
