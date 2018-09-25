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

namespace App\MasterData;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

/**
 * @property int $id
 * @property string $description
 */
class CompetitionState extends Model {

	const STATE_ENROLLMENT = 1;
	const STATE_TASTINGNUMBERS1 = 2;
	const STATE_TASTING1 = 3;
	const STATE_KDB = 4;
	const STATE_EXCLUDE = 5;
	const STATE_TASTINGNUMBERS2 = 6;
	const STATE_TASTING2 = 7;
	const STATE_SOSI = 8;
	const STATE_CHOOSE = 9;
	const STATE_CATALOGUE_NUMBERS = 10;
	const STATE_FINISHED = 11;

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $table = 'competition_state';

	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Convert competition state to German string
	 * 
	 * @return string
	 */
	public function getDescription() {
		switch ($this->description) {
			case 'ENROLLMENT':
				return '&Uuml;bernahme';
			case 'TASTINGNUMBERS1':
				return 'Kostnummern 1';
			case 'TASTING1':
				return '1. Verkostung';
			case 'KDB':
				return 'KdB Vergabe';
			case 'EXCLUDE':
				return 'Ausschluss';
			case 'TASTINGNUMBERS2':
				return 'Kostnummern 2';
			case 'TASTING2':
				return '2. Verkostung';
			case 'SOSI':
				return 'SoSi Vergabe';
			case 'CHOOSE':
				return 'Auswahl';
			case 'CATALOGUE_NUMBERS':
				return 'Katalognummern';
			case 'FINISHED':
				return 'abgeschlossen';
			default:
				Log::error('unknown competition state ' . $this->description);
				App::abort(500);
		}
	}

}
