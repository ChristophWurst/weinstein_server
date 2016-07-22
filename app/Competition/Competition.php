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

namespace App\Competition;

use Illuminate\Database\Eloquent\Model;
use App\AdministrateModel;
use App\Competition\Tasting\TastingStage;
use App\User;

class Competition extends Model implements AdministrateModel {

	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected $table = 'competition';

	/**
	 * Attributes allowed for mass assignment
	 * 
	 * @var array of string
	 */
	protected $fillable = [
	    'label',
	    'wuser_username',
	    'tastingstage_id',
	];

	/**
	 * Check if the user is authorized to administrate
	 * 
	 * @param User $user
	 * @return boolean
	 */
	public function administrates(User $user) {
		if ($user->admin) {
			return true;
		}
		if ($user->username === $this->wuser_username) {
			return true;
		}
		return false;
	}

	/**
	 * Get current tasting stage
	 * 
	 * @return TastingStage|null
	 */
	public function getTastingStage() {
		if (in_array($this->competitionstate->description, ['ENROLLMENT', 'TASTINGNUMBERS1', 'TASTING1'])) {
			return TastingStage::find(1);
		}
		if (in_array($this->competitionstate->description, ['TASTINGNUMBERS2', 'TASTING2'])) {
			return TastingStage::find(2);
		}
		return null;
	}

	public function enrollmentFinished() {
		$wines = $this->wines()->count();
		$winesWithNr = $this->wines()->whereNotNull('nr')->count();
		return $wines > 0 && $winesWithNr === $wines;
	}

	/**
	 * 1 competition : n applicant catalog entries
	 * 
	 * @return Relation
	 */
	public function addressCatalogue() {
		return $this->hasMany('AddressCatalogue', 'competition_id', 'id');
	}

	/**
	 * n competitions : 1 competition state
	 * 
	 * @return Relation
	 */
	public function competitionstate() {
		return $this->belongsTo('CompetitionState');
	}

	/**
	 * 1 competition : n tasting numbers
	 * 
	 * @return Relation
	 */
	public function tastingnumbers() {
		return $this->hasManyThrough('TastingNumber', 'Wine');
	}

	/**
	 * 1 competition : n tasting sessions
	 * 
	 * @return Relation
	 */
	public function tastingsessions() {
		return $this->hasMany('TastingSession');
	}

	/**
	 * n competitions : 1 user
	 * 
	 * @return Relation
	 */
	public function user() {
		return $this->belongsTo('User', 'wuser_username', 'username');
	}

	/**
	 * 1 competition : n wines
	 * 
	 * @return Relation
	 */
	public function wines() {
		return $this->hasMany('Wine');
	}

	/**
	 * 1 competition : n wines
	 * 
	 * @return Relation
	 */
	public function wine_details() {
		return $this->hasMany('WineDetails');
	}

}
