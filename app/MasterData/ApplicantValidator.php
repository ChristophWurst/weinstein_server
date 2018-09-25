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

namespace App\MasterData;

use App\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

class ApplicantValidator extends Validator {

	/**
	 * Models class name
	 *
	 * @var string
	 */
	protected $modelClass = Applicant::class;

	/**
	 * Get attribute names
	 * 
	 * @return array
	 */
	protected function getAttributeNames() {
		return array(
			'id' => 'Betriebsnummer',
			'association_id.' => 'Vereinsnummer',
			'label' => 'Bezeichnung',
			'title' => 'Titel',
			'firstname' => 'Vorname',
			'lastname' => 'Nachname',
			'phone' => 'Telefonnummer',
			'fax' => 'Faxnummer',
			'mobile' => 'Mobilnummer',
			'email' => 'Emailadresse',
			'web' => 'Webseite',
			'wuser_username' => 'Benutzer',
		);
	}

	/**
	 * Get rules for creating a new applicant
	 * 
	 * @return array
	 */
	protected function getCreateRules(array $data) {
		return array(
			'id' => 'Required|Integer|Min:10000|Unique:applicant',
			'label' => 'max:50',
			'title' => 'max:30',
			'firstname' => 'min:3|max:80',
			'lastname' => 'Required|min:3|max:80',
			'phone' => 'max:20',
			'fax' => 'max:20',
			'mobile' => 'max:20',
			'email' => 'email|max:50',
			'web' => 'max:50',
			'wuser_username' => 'nullable|eists:wuser,username',
		);
	}

	/**
	 * Get rules for updating an existing applicant
	 * 
	 * @return array
	 */
	protected function getUpdateRules(array $data, Model $applicant = null) {
		if (isset($data['id']) && $data['id'] == $applicant->id) {
			//id unchanged
			$rules = array();
		} else {
			$rules = array('id' => 'Required|Integer|Min:10000|Unique:applicant');
		}

		return array_merge($rules,
			array(
			'label' => 'max:50',
			'title' => 'max:30',
			'firstname' => 'min:3|max:80',
			'lastname' => 'Required|min:3|max:80',
			'phone' => 'max:20',
			'fax' => 'max:20',
			'mobile' => 'max:20',
			'email' => 'email|max:50',
			'web' => 'max:50',
			'wuser_username' => 'nullable|exists:wuser,username',
		));
	}

}
