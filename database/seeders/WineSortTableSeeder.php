<?php

namespace Database\Seeders;

use App\MasterData\WineSort;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
class WineSortTableSeeder extends Seeder {
    
	/**
	 * Insert new wine sort into database
	 * 
	 * @param string $name
	 * @param int $order
	 * @return WineSort
	 */
	public static function createWineSort($name, $order) {
		return WineSort::create(array(
			'name' => $name,
			'order' => $order,
		));
	}
    
	/**
	 * Run wine sort seeder
	 */
	public function run() {
		//delete existing wine sorts
		DB::table('winesort')->delete();
        
		for ($i = 1; $i <= 15; $i++) {
			$this->createWineSort("sort $i", $i);
		}
	}
    
}
