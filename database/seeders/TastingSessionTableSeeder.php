<?php

namespace Database\Seeders;

use App\MasterData\Competition;
use App\Tasting\TastingSession;
use Illuminate\Database\Seeder;

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
class TastingSessionTableSeeder extends Seeder
{
    /**
     * Insert new tasting session into database.
     *
     * @param int $nr
     * @param int $competition
     * @param int $tastingstage
     * @param string $username
     * @return TastingSession
     */
    public static function createTastingSession($nr, $competition, $tastingstage, $username)
    {
        return TastingSession::create([
                    'nr' => $nr,
                    'competition_id' => $competition,
                    'tastingstage_id' => $tastingstage,
                    'wuser_username' => $username,
                    'locked' => false,
        ]);
    }

    /**
     * Run tasting session seeder.
     */
    public function run()
    {
        foreach (Competition::all() as $competition) {
            for ($i = 1; $i <= rand(12, 20); $i++) {
                if ($i % 3 === 0) {
                    $username = 'user1';
                } elseif ($i % 5 === 0) {
                    $username = 'user2';
                } elseif ($i % 7 === 0) {
                    $username = 'user3';
                } elseif ($i % 8 === 0) {
                    $username = 'user4';
                } else {
                    $username = null;
                }
                $session = $this->createTastingSession($i, $competition->id, 1, $username);
                foreach (range(1, rand(1, 2)) as $comm) {
                    $commission = CommissionTableSeeder::createCommission($comm == 1 ? 'A' : 'B', $session->id);
                    foreach (range(1, rand(2, 10)) as $nr) {
                        TasterTableSeeder::createTaster($nr, 'Taster A'.$nr, rand(1, 10) !== 5, $commission->id);
                    }
                    foreach (range(1, rand(2, 10)) as $nr) {
                        TasterTableSeeder::createTaster($nr, 'Taster B'.$nr, rand(1, 10) !== 5, $commission->id);
                    }
                }
            }
        }
    }
}
