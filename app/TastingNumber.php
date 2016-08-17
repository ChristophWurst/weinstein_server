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

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property Wine $wine
 */
class TastingNumber extends Model {

	/**
	 * Table name
	 * 
	 * @var string 
	 */
	protected $table = 'tastingnumber';

	/**
	 * Attributes for mass assignment
	 * 
	 * @var type 
	 */
	protected $fillable = [
	    'wine_id',
	    'nr',
	    'tastingstage_id'
	];

	/**
	 * 1 tasting number : n tasting
	 * 
	 * @return Relation
	 */
	public function tastings() {
		return $this->hasMany('Tasting', 'tastingnumber_id', 'id');
	}

	/**
	 * n tasting number : 1 tasting stage
	 * @return Relation
	 */
	public function tastingstage() {
		return $this->belongsTo('TastingStage');
	}

	/**
	 * n tasting numbers : 1 wine
	 * 
	 * @return Relation
	 */
	public function wine() {
		return $this->belongsTo('Wine');
	}

	/**
	 * Scope tasting numbers to given tasting stage
	 * 
	 * @param \Illuminate\Database\Query $query
	 * @param TastingStage $tastingStage
	 * @return \Illuminate\Database\Query
	 */
	public function scopeTastingStage($query, TastingStage $tastingStage) {
		return $query->where('tastingstage_id', '=', $tastingStage->id);
	}

}
