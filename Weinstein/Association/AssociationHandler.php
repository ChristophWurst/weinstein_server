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

use App\Applicant;
use App\Association;
use App\User;
use Weinstein\Support\ActivityLog\ActivityLogger;

class AssociationHandler {

	/**
	 * Data provider
	 * 
	 * @var AssociationDataProvider
	 */
	private $dataProvider;

	/**
	 * Constructor
	 * 
	 * @param AssociationDataProvider $dataProvider
	 */
	public function __construct(AssociationDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Create an association
	 * 
	 * @param array $data
	 * @return Applicant
	 */
	public function create(array $data) {
		$associationValidator = new AssociationValidator($data);
		$associationValidator->validateCreate();
		$association = Association::create($data);
		ActivityLogger::log('Verein [' . $data['name'] . '] erstellt');
		return $association;
	}

	/**
	 * Update the association
	 * 
	 * @param Association $association
	 * @param array $data
	 * @return Applicant
	 */
	public function update(Association $association, array $data) {
		//only admin can change user
		if (!Auth::user()->admin) {
			$data['wuser_username'] = $association->wuser_username;
		}

		$associationValidator = new AssociationValidator($data, $association);
		$associationValidator->validateUpdate();
		$association->update($data);

		if (Auth::user()->admin && !isset($data['wuser_username'])) {
			$association->wuser_username = null;
			$association->save();
		}
		ActivityLogger::log('Verein [' . $association->name . '] bearbeitet');
		return $association;
	}

	/**
	 * Delete the association
	 * 
	 * @param Association $association
	 */
	public function delete(Association $association) {
		$name = $association->name;
		$association->delete();
		ActivityLogger::log('Verein [' . $association->name . '] gel&ouml;scht');
	}

	/**
	 * Get users associations
	 * 
	 * @return \Illuminate\Database\Eloquent\Collection
	 */
	public function getUsersAssociations(User $user) {
		if ($user->admin) {
			return $this->dataProvider->getAll();
		} else {
			return $this->dataProvider->getAll($user);
		}
	}

}
