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

namespace Weinstein\Applicant;

use Illuminate\Database\Eloquent\Model;
use Weinstein\Support\Validation\Validator as Validator;

class AddressValidator extends Validator {

	/**
	 * Models class name
	 * 
	 * @var string
	 */
	protected $modelClass = 'Address';

	/**
	 * Get attribute names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array(
		    'street' => 'Straße',
		    'nr' => 'Nr',
		    'zipcode' => 'PLZ',
		    'city' => 'Ort'
		);
	}

	/**
	 * Get rules for creating a new address
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return array(
		    'street' => 'max:100',
		    'nr' => 'max:20',
		    'zipcode' => 'required|integer|between:1000,9999',
		    'city' => 'max:70'
		);
	}

	protected function getUpdateRules(array $data, Model $model = null) {
		return $this->getCreateRules($data);
	}

}
