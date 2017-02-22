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

use App\Auth\Abilities\TastingSessionAbilities;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\TastingSession;
use Mockery;
use Test\TestCase;

class TastingSessionAbilitiesTest extends TestCase {

	use AbilitiesMock;

	/** @var TastingSessionAbilities */
	private $abilities;

	protected function setUp() {
		parent::setUp();

		$this->abilities = new TastingSessionAbilities();
	}

	public function testListTastersAsTastingSessionAdmin() {
		$user = $this->getUserMock();
		$tastingSession = Mockery::mock(TastingSession::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$tastingSession->shouldReceive('administrates')
			->with($user)
			->andReturn(true);
		$tastingSession->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_TASTING1);

		$allowed = $this->abilities->tasters($user, $tastingSession);

		$this->assertTrue($allowed);
	}

	public function testListTastersAsTastingSessionAdminButWrongComeptitionState() {
		$user = $this->getUserMock();
		$tastingSession = Mockery::mock(TastingSession::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$tastingSession->shouldReceive('administrates')
			->with($user)
			->andReturn(true);
		$tastingSession->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_KDB);

		$allowed = $this->abilities->tasters($user, $tastingSession);

		$this->assertFalse($allowed);
	}

	public function testListTastersAsNonTastingSessionAdmin() {
		$user = $this->getUserMock();
		$tastingSession = Mockery::mock(TastingSession::class);
		$tastingSession->shouldReceive('administrates')
			->with($user)
			->andReturn(false);

		$allowed = $this->abilities->tasters($user, $tastingSession);

		$this->assertFalse($allowed);
	}

}
