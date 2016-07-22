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

namespace Weinstein\Association;

use Illuminate\Database\Eloquent\Model as Model;
use Weinstein\Support\Validation\Validator;

class AssociationValidator extends Validator {

	/**
	 * Models class name
	 * 
	 * @var string 
	 */
	protected $modelClass = 'Association';

	/**
	 * Get attribute names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array(
		    'id' => 'Standnummer',
		    'name' => 'Bezeichnung',
		    'wuser_username' => 'Benutzer'
		);
	}

	/**
	 * Get rules for creating a new association
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return array(
		    'id' => 'Required|integer|min:1|unique:association,id',
		    'name' => 'Required|between:4,80|unique:association,name',
		    'wuser_username' => 'Exists:wuser,username',
		);
	}

	/**
	 * Get rules for updating an existing association
	 * 
	 * @param array $data
	 * @param \Weinstein\Association\Model $association
	 * @return array
	 */
	protected function getUpdateRules(array $data, Model $association = null) {
		//only check uniqueness of id if it was changed
		$idUnchanged = isset($data['id']) && $data['id'] == $this->model->id;
		//only check uniqueness of name if it was changed
		$nameUnchanged = isset($data['name']) && $data['name'] == $this->model->name;
		return array(
		    'id' => 'Required|integer|min:1' . ($idUnchanged ? '' : '|unique:association,id'),
		    'name' => 'Required|between:5,80' . ($nameUnchanged ? '' : '|unique:association,name'),
		    'wuser_username' => 'Exists:wuser,username',
		);
	}

}
