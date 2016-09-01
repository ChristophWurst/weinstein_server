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

namespace Test\Unit\Auth\Abilities;

use App\Auth\Abilities\ActivityLogAbilities;
use App\MasterData\User;
use Mockery;
use Test\TestCase;

class ActivityLogAbilitiesTest extends TestCase {

	/** @var ActivityLogAbilities */
	private $abilities;

	protected function setUp() {
		parent::setUp();

		$this->abilities = new ActivityLogAbilities();
	}

	public function testViewAsAdmin() {
		$user = Mockery::mock(User::class);

		$user->shouldReceive('isAdmin')->once()->andReturn(true);

		$this->assertTrue($this->abilities->view($user));
	}

	public function testViewAsNonAdmin() {
		$user = Mockery::mock(User::class);

		$user->shouldReceive('isAdmin')->once()->andReturn(false);

		$this->assertFalse($this->abilities->view($user));
	}

}
