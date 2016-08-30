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
class AssociationTableSeeder extends Seeder {

	/**
	 * Insert new association into database
	 * 
	 * @param (big)int $id
	 * @param string $name
	 * @param int|null $username
	 * @return Association
	 */
	private static function createAssociation($id, $name, $username) {
		return Association::create(array(
			'id' => $id,
			'name' => $name,
			'wuser_username' => $username,
		));
	}

	/**
	 * Run association seeder
	 */
	public function run() {
		//delete all associations
		DB::table('association')->delete();

		$this->createAssociation(1, 'association 1', null);
		$this->createAssociation(2, 'association 2', 'user1');
		$this->createAssociation(3, 'association 3', 'user2');
		$this->createAssociation(4, 'association 4', null);
		$this->createAssociation(5, 'association 5', null);
		$this->createAssociation(6, 'association 6', null);
		$this->createAssociation(7, 'association 7', null);
		$this->createAssociation(8, 'association 8', 'user1');
		$this->createAssociation(9, 'association 9', 'user2');
		$this->createAssociation(10, 'association 10', 'user3');
		$this->createAssociation(11, 'association 11', 'admin1');
		$this->createAssociation(12, 'association 12', null);
		$this->createAssociation(13, 'association 13', null);
		$this->createAssociation(14, 'association 14', 'admin1');
		$this->createAssociation(15, 'association 15', 'admin2');
		$this->createAssociation(16, 'association 16', null);
		$this->createAssociation(17, 'association 17', null);
		$this->createAssociation(18, 'association 18', null);
		$this->createAssociation(19, 'association 19', null);
		$this->createAssociation(20, 'association 20', null);
	}

}
