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

use App\Exception\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class FileValidator {

	/**
	 * File for validation
	 * 
	 * @var UploadedFile
	 */
	protected $file = null;

	/**
	 * Allowed mime types
	 *  
	 * @var string
	 */
	protected $mimeTypes = '';

	/**
	 * Files title for validation error messages
	 * 
	 * @var type 
	 */
	protected $fileTitle = 'Datei';

	/**
	 * Get validation error messages
	 * 
	 * @return array
	 */
	protected function getErrorMessages() {
		return array(
			'file.required' => 'Keine Datei ausgew&auml;hlt',
		);
	}

	/**
	 * Constructor
	 * 
	 * @param UploadedFile $file
	 */
	public function __construct(UploadedFile $file) {
		$this->file = $file;
	}

	/**
	 * Validate the uploaded file
	 * 
	 * @throws ValidationException
	 */
	public function validate() {
		$data = array(
			'file' => $this->file,
		);
		$rules = array(
			'file' => 'mimes:' . $this->mimeTypes,
		);
		$names = array(
			'file' => $this->fileTitle,
		);
		$validator = Validator::make($data, $rules, $this->getErrorMessages(), $names);
		if ($validator->fails()) {
			throw new ValidationException($validator->messages());
		}
	}

}
