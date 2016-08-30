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
class TastingNumberTableSeeder extends Seeder {
    
	/**
	 * Insert new tasting number into database
	 * 
	 * @param int $nr
	 * @param int $wine
	 * @param int $tastingstage
	 * @return TastingNumber
	 */
	public static function createTastingNumber($nr, $wine, $tastingstage) {
		return TastingNumber::create(array(
			'nr' => $nr,
			'wine_id' => $wine,
			'tastingstage_id' => $tastingstage,
		));
	}
    
	/**
	 * Run tasting number seeder
	 */
	public function run() {
		//delete existing tasting numbers
		DB::table('tastingnumber')->delete();
        
		foreach (Competition::all() as $competition) {
			$i = 1;
			foreach ($competition->wines as $wine) {
				$this->createTastingNumber($i, $wine->id, 1);
				$i++;
			}
		}
	}
}
