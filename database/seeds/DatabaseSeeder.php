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

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
     * Delete all tables data
     */
    private function deleteExistingData() {
        DB::table('tasting')->delete();
        DB::table('taster')->delete();
        DB::table('commission')->delete();
        DB::table('tastingsession')->delete();
        DB::table('tastingnumber')->delete();
        DB::table('wine')->delete();
        DB::table('winesort')->delete();
        DB::table('competition')->delete();
        DB::table('applicant')->delete();
        DB::table('address')->delete();
        DB::table('association')->delete();
        DB::table('wuser')->delete();
    }
    
    /**
     * Run all database seeders
     */
    private function runSeeders() {
        $this->call('UserTableSeeder');
        $this->call('AssociationTableSeeder');
        $this->call('ApplicantTableSeeder');
        $this->call('CompetitionTableSeeder');
        $this->call('WineSortTableSeeder');
        $this->call('WineTableSeeder');
        $this->call('TastingNumberTableSeeder');
        $this->call('TastingSessionTableSeeder');
        $this->call('TasterTableSeeder');
        $this->call('TastingTableSeeder');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        Model::unguard();

        $this->deleteExistingData();
        $this->runSeeders();

        Model::reguard();
    }
}
