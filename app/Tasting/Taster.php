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

/**
 * @property int $id
 * @property int $commission_id
 * @property Commission $commission
 * @property int $nr
 * @property string $name
 * @property bool $active
 */
class Taster extends Model {

	/**
	 * Table name
	 * 
	 * @var string 
	 */
	protected $table = 'taster';

	/**
	 * Attributes for mass assignment
	 * 
	 * @var array 
	 */
	protected $fillable = [
		'nr',
		'tasterside_id',
		'commission_id',
		'active',
		'name',
	];

	/**
	 * n tasters : 1 commision
	 * 
	 * @return Relation
	 */
	public function commission() {
		return $this->belongsTo(Commission::class);
	}

	/**
	 * Scope only active tasters
	 * 
	 * @param Query $query
	 * @return Query
	 */
	public function scopeActive($query) {
		return $query->where('active', '=', true);
	}

	/**
	 * 1 taster : 1 statistic
	 * 
	 * @return Relation
	 */
	public function statistic() {
		return $this->hasOne(TasterStatistic::class);
	}

	/**
	 * 1 taster : n tastings
	 * 
	 * @return Relation
	 */
	public function tastings() {
		return $this->hasMany(Tasting::class);
	}

}
