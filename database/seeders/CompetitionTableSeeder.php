<?php

namespace Database\Seeders;

use App\MasterData\Competition;
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
class CompetitionTableSeeder extends Seeder {
    
	/**
	 * Insert new competition into database
	 * 
	 * @param string $label
	 * @param int $competitionState
	 * @param string|null $username
	 * @return Competition
	 */
	public static function createCompetition($label, $competitionState, $username) {
		return Competition::create(array(
			'label' => $label,
			'competition_state_id' => $competitionState,
			'wuser_username' => $username,
		));
	}
    
	/**
	 * Run competition seeder
	 */
	public function run() {
		DB::table('competition')->delete();
        
		$this->createCompetition('comp1', 1, 'user1');
	}
    
}
