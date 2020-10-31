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

use App\Auth\Abilities\CatalogueAbilities;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use Mockery;
use Test\TestCase;

class CatalogueAbilitiesTest extends TestCase {

	use AbilitiesMock;

	/** @var CatalogueAbilities */
	private $abilities;

	protected function setUp(): void {
		parent::setUp();

		$this->abilities = new CatalogueAbilities();
	}

	public function testCreateNotACompetitionAdmin() {
		$user = $this->getUserMock();
		$competition = Mockery::mock(Competition::class);

		$competition->shouldReceive('administrates')
			->with($user)
			->once()
			->andReturn(false);

		$this->assertFalse($this->abilities->create($user, $competition));
	}

	public function testCreateAsCompetitionAdminButWrongState() {
		$user = $this->getUserMock();
		$competition = Mockery::mock(Competition::class);

		$competition->shouldReceive('administrates')
			->with($user)
			->once()
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->with('competition_state_id')
			->andReturn(CompetitionState::STATE_EXCLUDE);

		$this->assertFalse($this->abilities->create($user, $competition));
	}

	public function testCreateAsCompetitionAdmin() {
		$user = $this->getUserMock();
		$competition = Mockery::mock(Competition::class);

		$competition->shouldReceive('administrates')
			->with($user)
			->once()
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->with('competition_state_id')
			->andReturn(CompetitionState::STATE_FINISHED);

		$this->assertTrue($this->abilities->create($user, $competition));
	}

}
