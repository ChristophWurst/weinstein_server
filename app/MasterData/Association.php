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
use App\MasterData\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $wuser_username
 * @property User $user
 */
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
		'email',
		'wuser_username'
	];

	/**
	 * The attributes that should be hidden for arrays/json.
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at',
		'updated_at',
	];

	/**
	 * Check if the given user is authorized to administrate
	 * 
	 * @param User $user
	 * @return bool
	 */
	public function administrates(User $user) {
		if ($user->isAdmin()) {
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
		return $this->hasMany(Applicant::class);
	}

	/**
	 * n associations : 1 user
	 * 
	 * @return Relation
	 */
	public function user() {
		return $this->belongsTo(User::class, 'wuser_username', 'username');
	}

}
