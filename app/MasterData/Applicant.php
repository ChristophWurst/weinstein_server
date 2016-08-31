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
use App\Wine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property string $wuser_username
 * @property User $user
 * @property int $association_id
 * @property Association $association
 * @property int $address_id
 * @property Address $address
 * @property string $label
 * @property string $title
 * @property string $firstname
 * @property string $lastname
 * @property string $phone
 * @property string $fax
 * @property string $mobile
 * @property string $email
 * @property string $web
 */
class Applicant extends Model implements AdministrateModel {

	/**
	 * table name
	 * 
	 * @var string
	 */
	protected $table = 'applicant';

	/**
	 * attributes for mass assignment
	 * 
	 * @var array of string
	 */
	protected $fillable = [
		'id',
		'association_id',
		'wuser_username',
		'label',
		'title',
		'firstname',
		'lastname',
		'address_id',
		'phone',
		'fax',
		'mobile',
		'email',
		'web'
	];

	/**
	 * primary key must not be incremented
	 * 
	 * @var boolean
	 */
	protected $incremented = false;

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
		if ($this->wuser_username === $user->username) {
			return true;
		}
		$association = $this->association;
		if ($association && $association->administrates($user)) {
			return true;
		}
		return false;
	}

	/**
	 * 
	 * @return string
	 */
	public function getSelectLabelAttribute() {
		return htmlentities($this->attributes['id']
			. ' - '
			. $this->attributes['lastname']
			. ' '
			. $this->attributes['firstname']
			. ' - '
			. $this->address->city);
	}

	/**
	 * 1 applicant : 1 address
	 * 
	 * @return BelongsTo
	 */
	public function address() {
		return $this->belongsTo(Address::class);
	}

	/**
	 * n applicants : 1 association
	 * 
	 * @return BelongsTo
	 */
	public function association() {
		return $this->belongsTo(Association::class);
	}

	/**
	 * n applicants : 1 user
	 * 
	 * @return BelongsTo
	 */
	public function user() {
		return $this->belongsTo(User::class, 'wuser_username', 'username');
	}

	/**
	 * 1 applicant : n wines
	 * 
	 * @return Relation
	 */
	public function wines() {
		return $this->hasMany(Wine::class);
	}

}
