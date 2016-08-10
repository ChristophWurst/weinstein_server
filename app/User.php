<?php

use App\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Hash;

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

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable;

use CanResetPasswordContract;

	/**
	 * table name
	 * 
	 * @var string
	 */
	protected $table = 'wuser';

	/**
	 * primary key of table user
	 * 
	 * @var string
	 */
	protected $primaryKey = 'username';

	/**
	 * attributes allowed for mass assignment
	 * 
	 * @var array of string
	 */
	protected $fillable = array(
	    'username',
	    'password',
	    'admin'
	);

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array(
	    'password'
	);

	public function administrates(User $user) {
		if ($user->admin) {
			return true;
		}
		if ($user->username === $this->username) {
			return true;
		}
		return false;
	}

	/**
	 * Password mutator
	 * 
	 * @param string $password
	 */
	public function setPasswordAttribute($password) {
		$this->attributes['password'] = Hash::make($password);
	}

	/**
	 * 1 user : n activity logs
	 * 
	 * @return Relation
	 */
	public function activitylogs() {
		return $this->hasMany('ActivityLog', 'wuser_username', 'username');
	}

	/**
	 * 1 user : n applicants
	 * 
	 * @return Relation
	 */
	public function applicants() {
		return $this->hasMany('Applicant', 'wuser_username', 'username');
	}

	/**
	 * 1 user : n associations : m applicants
	 * => 1 user : n*m applicants
	 * 
	 * @return Relation
	 */
	public function associationApplicants() {
		return $this->hasManyThrough('Applicant', 'Association', 'wuser_username');
	}

	/**
	 * 1 user : n associations relation
	 * 
	 * @return Relation
	 */
	public function associations() {
		return $this->hasMany('Association', 'wuser_username', 'username');
	}

	/**
	 * 1 user : n competitions
	 * 
	 * @return Relation
	 */
	public function competitions() {
		return $this->hasMany('Competition', 'wuser_username', 'username');
	}

	/**
	 * 1 user : n tasting sessions
	 * 
	 * @return Relation
	 */
	public function tastingsessions() {
		return $this->hasMany('TastingSession', 'wuser_username', 'username');
	}

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier() {
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword() {
		return $this->password;
	}

	/**
	 * Get token value
	 * 
	 * @return string
	 */
	public function getRememberToken() {
		return $this->remember_token;
	}

	/**
	 * Set token value
	 * 
	 * @param string $value
	 */
	public function setRememberToken($value) {
		$this->remember_token = $value;
	}

	/**
	 * Get token attribute name
	 * 
	 * @return string
	 */
	public function getRememberTokenName() {
		return 'remember_token';
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail() {
		return $this->email;
	}

	public function getEmailForPasswordReset() {
		throw new Exception("method not implemented");
	}

}
