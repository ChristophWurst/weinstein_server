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
class WineTableSeeder extends Seeder {

	private function randomWineSort() {
		$s = rand(1, WineSort::count());
		$i = 1;
		foreach (WineSort::all() as $ws) {
			if ($i == $s) {
				return $ws->id;
			}
			$i++;
		}
		return 1;
	}

	/**
	 * Insert new wine into database
	 * 
	 * @param int $nr
	 * @param string $label
	 * @param int $vintage
	 * @param float $alcohol
	 * @param float $alcoholtot
	 * @param float $sugar
	 * @param int $competition
	 * @param int $applicant
	 * @param int $association
	 * @param int $winesort
	 * @param int $winequality
	 * @return Wine
	 */
	public static function createWine($nr, $label, $vintage, $alcohol, $alcoholtot, $sugar, $competition, $applicant, $association, $winesort, $winequality) {
		return Wine::create(array(
					'nr' => $nr,
					'label' => $label,
					'vintage' => $vintage,
					'alcohol' => $alcohol,
					'alcoholtot' => $alcoholtot,
					'sugar' => $sugar,
					'competition_id' => $competition,
					'association_id' => $association,
					'applicant_id' => $applicant,
					'winesort_id' => $winesort,
					'winequality_id' => $winequality,
		));
	}

	public function run() {
		//delete existing wines
		DB::table('wine')->delete();

		$nr = 1;
		foreach (Competition::all() as $competition) {
			foreach (Applicant::all() as $applicant) {
				for ($i = 1; $i <= rand(3, 10); $i++) {
					$this->createWine($nr, "wine $i", 2000 + $i, 1.5 * $i, 2.04 * $i, 0.23 * $i, $competition->id, $applicant->id, $applicant->association->id, $this->randomWineSort(), 2);
					$nr++;
				}
			}
		}
	}

}
