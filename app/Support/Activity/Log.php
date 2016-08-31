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

namespace App\Support\Activity;

use App\MasterData\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property string $message
 * @property string $wuser_username
 * @property User $user
 */
class Log extends Model {

	/**
	 * table name
	 * 
	 * @var string
	 */
	protected $table = 'activitylog';

	/**
	 * attributs for mass assignment
	 * 
	 * @var array of string
	 */
	protected $fillable = [
		'message',
		'wuser_username',
	];

	/**
	 * n activity logs : 1 user
	 * 
	 * @return Relation
	 */
	public function user() {
		return $this->belongsTo(User::class, 'wuser_username', 'username');
	}

}
