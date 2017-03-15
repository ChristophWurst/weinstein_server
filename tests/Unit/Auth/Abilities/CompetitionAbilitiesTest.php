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

use App\Auth\Abilities\CompetitionAbilities;
use App\MasterData\CompetitionState;
use Mockery;
use Test\TestCase;

class CompetitionAbilitiesTest extends TestCase {

	use AbilitiesMock;

	/** @var CompetitionAbilities */
	private $abilities;

	protected function setUp() {
		parent::setUp();

		$this->abilities = new CompetitionAbilities();
	}

	public function testShow() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);

		$this->assertTrue($this->abilities->show($user, $competition));
	}

	public function testReset() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();

		$this->assertFalse($this->abilities->reset($user, $competition));
	}

	public function testCompleteTastingNumbersNoAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeTasingNumbers($user, $competition));
	}

	public function testCompleteTastingNumbersWrongTastingStage() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->twice()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->twice()
			->with('id')
			->andReturn(CompetitionState::STATE_CHOOSE);

		$this->assertFalse($this->abilities->completeTasingNumbers($user, $competition));
	}

	public function testCompleteTastingNumbersTastingNumbers1() {
		// TODO: move to service/handler
	}

	public function testCompleteTastingNumbersTastingNumbers2() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->twice()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->twice()
			->with('id')
			->andReturn(CompetitionState::STATE_TASTINGNUMBERS2);

		$this->assertTrue($this->abilities->completeTasingNumbers($user, $competition));
	}

	public function testCompleteTastingNoAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeTasting($user, $competition));
	}

	public function testCompleteTasting1() {
		// TODO
	}

	public function testCompleteTasting2() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->twice()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->twice()
			->with('id')
			->andReturn(CompetitionState::STATE_TASTING2);

		$this->assertTrue($this->abilities->completeTasting($user, $competition));
	}

	public function testCompleteTastingWrongTastingStage() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->twice()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->twice()
			->with('id')
			->andReturn(CompetitionState::STATE_ENROLLMENT);

		$this->assertFalse($this->abilities->completeTasting($user, $competition));
	}

	public function testCompleteKdbNonAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteKdbWrongState() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_KDB)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteKdb() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_KDB)
			->andReturn(true);

		$this->assertTrue($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteExcludedNonAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteExcludedWrongState() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_EXCLUDE)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeExcluded($user, $competition));
	}

	public function testCompleteExcluded() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_EXCLUDE)
			->andReturn(true);

		$this->assertTrue($this->abilities->completeExcluded($user, $competition));
	}

	public function testCompleteSosiNonAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteSosiWrongState() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_SOSI)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeSosi($user, $competition));
	}

	public function testCompleteSosi() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_SOSI)
			->andReturn(true);

		$this->assertTrue($this->abilities->completeSosi($user, $competition));
	}

	public function testCompleteChoosingNonAdmin() {
		$user = $this->getUserMock();
		$competition = $this->getCompetitionMock();
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeKdb($user, $competition));
	}

	public function testCompleteChoosingWrongState() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_CHOOSE)
			->andReturn(false);

		$this->assertFalse($this->abilities->completeChoosing($user, $competition));
	}

	public function testCompleteChoosing() {
		$user = $this->getAdminMock();
		$competition = $this->getCompetitionMock();
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('is')
			->once()
			->with(CompetitionState::STATE_CHOOSE)
			->andReturn(true);

		$this->assertTrue($this->abilities->completeChoosing($user, $competition));
	}

}
