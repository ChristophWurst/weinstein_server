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

namespace Test\Unit\MasterData;

use App\MasterData\Association;
use App\MasterData\User;
use Test\TestCase;

class AssociationTest extends TestCase {

	public function testNoAdmin() {
		$user = new User(array(
			'username' => 'test123',
			'admin' => false,
		));
		$association = new Association();

		$this->assertFalse($association->administrates($user));
	}

	public function testAssociationAdmin() {
		$user = new User(array(
			'username' => 'test123',
			'admin' => false,
		));
		$association = new Association(array(
			'wuser_username' => 'test123',
		));

		$this->assertTrue($association->administrates($user));
	}

	public function testAdmin() {
		$user = new User(array(
			'username' => 'test123',
			'admin' => true,
		));
		$association = new Association(array(
			'wuser_username' => 'test321',
		));

		$this->assertSame(true, $association->administrates($user));
	}

}
