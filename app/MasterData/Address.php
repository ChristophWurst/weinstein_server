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

/**
 * @property int $id
 * @property int $zipcode
 * @property string $city
 * @property int $street
 * @property string $nr
 */
class Address extends Model {

	/**
	 * table name
	 * 
	 * @var string
	 */
	protected $table = 'address';

	/**
	 * attributs for mass assignment
	 * 
	 * @var array of string
	 */
	protected $fillable = [
		'street',
		'nr',
		'zipcode',
		'city'
	];

	/**
	 * 1 address : 1 applicant
	 * 
	 * @return type
	 */
	public function Applicant() {
		return $this->hasOne('Applicant');
	}

}
