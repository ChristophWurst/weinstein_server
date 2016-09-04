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

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception {

	/**
	 * Errors
	 * 
	 * @var MessageBag
	 */
	private $errors = null;

	/**
	 * Constructor
	 * 
	 * @param MessageBag $errors
	 */
	public function __construct(MessageBag $errors = null) {
		parent::__construct("Validation Error", 0, null);
		if (is_null($errors)) {
			$this->errors = new MessageBag();
		} else {
			$this->errors = $errors;
		}
	}

	/**
	 * Get validation error messages
	 * 
	 * @return MessageBag
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Merge another ValidationException with this instance
	 * 
	 * @param ValidationException $ve
	 * @return ValidationException
	 */
	public function merge(ValidationException $ve) {
		$this->errors = $this->errors->merge($ve->getErrors());
		return $this;
	}

}
