<?php

namespace Database\Seeders;

use App\Tasting\Tasting;
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
class TastingTableSeeder extends Seeder {

	/**
	 * Insert new tasting into database
	 * 
	 * @param float $rating
	 * @param int $taster
	 * @param int $tastingnumber
	 * @return Tasting
	 */
	public static function createTasting($rating, $taster, $tastingnumber) {
		return Tasting::create(array(
			'rating' => $rating,
			'taster_id' => $taster,
			'tastingnumber_id' => $tastingnumber,
		));
	}

	/**
	 * Run tasting seeder
	 *
	 * @todo finish implementation
	 *
	 */
	public function run() {
		//delete existing tastings
		DB::table('tasting')->delete();
        
		//TODO: finish implementation
	}

}
