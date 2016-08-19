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

namespace App\Tasting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class Commission extends Model {

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $table = 'commission';

	/**
	 * Attributes for mass assignment
	 * 
	 * @var array
	 */
	protected $fillable = [
	    'side',
	    'tastingsession_id',
	];

	/**
	 * 1 commission : 1 statistic
	 * 
	 * @return Relation
	 */
	public function statistic() {
		return $this->hasOne('App\Tasting\CommissionStatistic');
	}

	/**
	 * 1 commission : n Taster
	 * 
	 * @return Relation
	 */
	public function tasters() {
		return $this->hasMany('Taster', 'commission_id', 'id');
	}

	/**
	 * 1..2 commissions : 1 tasting session
	 * 
	 * @return Relation
	 */
	public function tastingsession() {
		return $this->belongsTo('TastingSession');
	}

}
