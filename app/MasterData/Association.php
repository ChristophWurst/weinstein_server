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

use App\AdministrateModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User;

class Association extends Model implements AdministrateModel {

	/**
	 * table name
	 * 
	 * @var string
	 */
	protected $table = 'association';

	/**
	 * attributs for mass assigment
	 * 
	 * @var array of string
	 */
	protected $fillable = [
		'id',
		'name',
		'wuser_username'
	];

	/**
	 * Check if the given user is authorized to administrate
	 * 
	 * @param User $user
	 * @return bool
	 */
	public function administrates(User $user) {
		if ($user->admin) {
			return true;
		}
		return $this->wuser_username === $user->username;
	}

	/**
	 * 
	 * @return string
	 */
	public function getSelectLabelAttribute() {
		return $this->id . ' - ' . $this->name;
	}

	/**
	 * 1 association : n applicants
	 * 
	 * @return Relation
	 */
	public function applicants() {
		return $this->hasMany('Applicant');
	}

	/**
	 * n associations : 1 user
	 * 
	 * @return Relation
	 */
	public function user() {
		return $this->belongsTo('User', 'wuser_username', 'username');
	}

}
