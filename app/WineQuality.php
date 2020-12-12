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
 * @property int $id
 * @property string $label
 * @property string $abbr
 */
class WineQuality extends Model {

	public $timestamps = false;

	/**
	 * Table name
	 * 
	 * @var string 
	 */
	protected $table = 'winequality';

	/**
	 * 
	 * @return string
	 */
	public function getSelectLabelAttribute() {
		return $this->id . ' - ' . $this->label;
	}

	/**
	 * 1 wine quality : n wines
	 * 
	 * @return Relation
	 */
	public function wines() {
		return $this->hasMany(Wine::class);
	}

}
