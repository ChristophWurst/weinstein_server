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

use App\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

class TasterValidator extends Validator {

	/**
	 * Models class name
	 *
	 * @var array
	 */
	protected $modelClass = Taster::class;

	/**
	 * Get attributes names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array(
			'commission_id' => 'Kommission',
			'name' => 'Name',
		);
	}

	/**
	 * Get rules for creating a new taster
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return [
			'commission_id' => 'required|exists:commission,id',
			'name' => 'required|min:1|max:70',
			'active' => 'bool',
		];
	}

	protected function getUpdateRules(array $data, Model $model = null) {
		return [
			'name' => 'required|min:1|max:70',
			'active' => 'bool',
		];
	}

}
