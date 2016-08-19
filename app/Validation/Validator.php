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

namespace App\Validation;

use App\Exceptions\ValidationException;
use App\Exceptions\ValidationModelMissingException;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Validator {

	/**
	 * Models class name
	 * 
	 * @var string
	 */
	protected $modelClass = null;

	/**
	 * Input form data
	 * 
	 * @var array
	 */
	protected $data = array();

	/**
	 * Model needed for creating some update rules
	 * 
	 * @var Model
	 */
	protected $model = null;

	/**
	 * Get attribute names as array
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array();
	}

	/**
	 * Get validation error messages
	 * 
	 * @return array
	 */
	protected function getErrorMessages() {
		return array();
	}

	/**
	 * Get rules for creating a new model
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return array();
	}

	/**
	 * Get rules for updating an existing model
	 * 
	 * @param array $data
	 * @param Model $model
	 * @return array
	 */
	protected function getUpdateRules(array $data, Model $model = null) {
		return array();
	}

	/**
	 * Prepare input data for validation
	 * 
	 * @param array $data
	 * @return array
	 */
	protected function prepareData(array $data) {
		return $data;
	}

	/**
	 * Constructor
	 * 
	 * @param array $data
	 * @param Model $model
	 */
	public function __construct(array $data, Model $model = null) {
		$this->data = $data;
		if ($model && !$model instanceof $this->modelClass) {
			throw new InvalidArgumentException;
		}
		$this->model = $model;
	}

	/**
	 * Validate creation of a new model
	 * 
	 * @throws ValidationException
	 */
	public function validateCreate() {
		$validator = \Validator::make($this->data, $this->getCreateRules($this->data), $this->getErrorMessages(), $this->getAttributeNames());
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}
	}

	/**
	 * Validate update of an existing model
	 * 
	 * @throws ValidationException
	 */
	public function validateUpdate() {
		if (!$this->model) {
			throw new ValidationModelMissingException;
		}
		$validator = \Validator::make($this->data, $this->getUpdateRules($this->data, $this->model), $this->getErrorMessages(), $this->getAttributeNames());
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}
	}

}
