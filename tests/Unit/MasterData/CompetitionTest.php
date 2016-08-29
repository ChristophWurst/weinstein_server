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
use App\MasterData\Competition;
use App\MasterData\User;

class CompetitionTest extends TestCase {

	use Way\Tests\ModelHelpers;

	public function testNoAdmin() {
		$user = new User(array(
			'username' => 'user123',
			'admin' => false,
		));
		$competition = new Competition();

		$this->assertFalse($competition->administrates($user));
	}

	public function testCompetitionAdmin() {
		$user = new User(array(
			'username' => 'test123',
			'admin' => false,
		));
		$competition = new Competition(array(
			'wuser_username' => 'test123',
		));

		$this->assertTrue($competition->administrates($user));
	}

	public function testAdmin() {
		$admin = new User(array(
			'admin' => true,
		));
		$competition = new Competition();

		$this->assertTrue($competition->administrates($admin));
	}

	public function testBelongsToCompetitionState() {
		$this->assertBelongsTo('competitionstate', Competition::class);
	}

	public function testBelongsToUser() {
		$this->assertBelongsTo('user', Competition::class);
	}

	public function testHasManyTastingSessions() {
		$this->assertHasMany('tastingsessions', Competition::class);
	}

	public function testHasManyWines() {
		$this->assertHasMany('wines', Competition::class);
	}

}
