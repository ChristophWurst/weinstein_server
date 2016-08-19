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

use App\AdministrateModel;
use App\MasterData\Competition;
use App\Tasting\TastingStage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;

/**
 * @property Competition $competition
 * @property Collection $tasters
 */
class TastingSession extends Model implements AdministrateModel {

	/**
	 * Table name
	 * 
	 * @var string 
	 */
	protected $table = 'tastingsession';

	/**
	 * Attributes for mass assignment
	 * 
	 * @var array 
	 */
	protected $fillable = [
		'date',
		'competition_id',
		'wuser_username',
		'tastingstage_id',
		'locked',
	];

	/**
	 * Check if user is authorized to administrate
	 * 
	 * @param User $user
	 * @return boolean
	 */
	public function administrates(User $user) {
		if ($user->admin) {
			return true;
		}
		$sessUser = $this->user;
		if ($sessUser && ($sessUser->username === $user->username)) {
			return true;
		}
		if ($this->competition->administrates($user)) {
			return true;
		}
		return false;
	}

	/**
	 * Get nr of active tasters
	 * 
	 * @return int
	 */
	public function GetActiveTastersCount() {
		$count = 0;
		foreach ($this->commissions as $commission) {
			$count += $commission->tasters()->active()->count();
		}
		return $count;
	}

	public function deletable() {
		return $this->tasters()->count() === 0;
	}

	/**
	 * n sessions : 1 competition
	 * 
	 * @return Relation
	 */
	public function competition() {
		return $this->belongsTo('Competition');
	}

	/**
	 * 1 tasting session : 1..2 commissions
	 * 
	 * @return Relation
	 */
	public function commissions() {
		return $this->hasMany('Commission', 'tastingsession_id', 'id');
	}

	/**
	 * 1 tasting session : n tasted wines
	 * 
	 * @return Relation
	 */
	public function tastedwines() {
		return $this->hasMany('TastedWine', 'tastingsession_id', 'id');
	}

	/**
	 * 1 tasting session : 1..2 commission : n taster
	 * 
	 * @return Relation
	 */
	public function tasters() {
		return $this->hasManyThrough('Taster', 'Commission', 'tastingsession_id', 'commission_id');
	}

	/**
	 * n tasting sessions : 1 tasting stage
	 * 
	 * @return Relation
	 */
	public function tastingstage() {
		return $this->belongsTo('TastingStage');
	}

	/**
	 * n sessions : 1 user
	 * 
	 * @return Relation
	 */
	public function user() {
		return $this->belongsTo('User', 'wuser_username', 'username');
	}

	/**
	 * scope tasting sessions of a given tasting stage
	 * 
	 * @param type $query
	 * @param TastingStage $ts
	 * @return type
	 */
	public function scopeOfTastingStage($query, TastingStage $ts) {
		return $query->where('tastingstage_id', '=', $ts->id);
	}

}
