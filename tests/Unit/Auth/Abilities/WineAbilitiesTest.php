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

use App\Auth\Abilities\WineAbilities;
use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Wine;
use Mockery;
use Test\TestCase;

class WineAbilitiesTest extends TestCase {

	use AbilitiesMock;

	/** @var WineAbilities */
	private $abilities;

	protected function setUp() {
		parent::setUp();

		$this->abilities = new WineAbilities();
	}

	public function testShow() {
		$user = Mockery::mock(User::class);
		$wine = Mockery::mock(Wine::class);
		$wine->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);

		$allowed = $this->abilities->show($user, $wine);

		$this->assertFalse($allowed);
	}

	public function testCreate() {
		$user = $this->getUserMock();
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_ENROLLMENT);

		$allowed = $this->abilities->create($user, $competition);

		$this->assertTrue($allowed);
	}

	public function testCreateWithWrongCompetitionState() {
		$user = $this->getUserMock();
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);

		$allowed = $this->abilities->create($user, $competition);

		$this->assertFalse($allowed);
	}

	/**
	 * Simulate a user updating the sosi state (kdb remains)
	 */
	public function testUpdateNotAllowedForWineAdministrator() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$competition = Mockery::mock(Competition::class);
		$data = [
			'id' => 23,
			'kdb' => true,
			'sosi' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('kdb')
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('sosi')
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(false);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertFalse($allowed);
	}

	/**
	 * Simulate a user updating the sosi state (kdb remains)
	 */
	public function testUpdateAllowedForCompetitionAdministrator() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$competition = Mockery::mock(Competition::class);
		$data = [
			'id' => 23,
			'kdb' => true,
			'sosi' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('kdb')
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('sosi')
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertTrue($allowed);
	}

	/**
	 * Simulate a user updating nothing
	 */
	public function testUpdateAllowedIfNothingChanges() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$data = [
			'id' => 23,
			'kdb' => true,
			'sosi' => true,
			'chosen' => true,
			'excluded' => false,
		];
		$wine->shouldReceive('getAttribute')
			->with('kdb')
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('sosi')
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('excluded')
			->andReturn(false);
		$wine->shouldReceive('administrates')
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertTrue($allowed);
	}

	/**
	 * Simulate a user updating a wine where they are not association admin
	 */
	public function testUpdateForbiddenIfNotAssociationAdmin() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$competition = Mockery::mock(Competition::class);
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('applicant')
			->andReturn($applicant);
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$association->shouldReceive('administrates')
			->andReturn(false);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertFalse($allowed);
	}

	/**
	 * Simulate a user updating a wine where they are association admin
	 */
	public function testUpdateAllowedIfUserIsAssociationAdmin() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$competition = Mockery::mock(Competition::class);
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('applicant')
			->andReturn($applicant);
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$association->shouldReceive('administrates')
			->andReturn(true);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertTrue($allowed);
	}

	/**
	 * Simulate a user updating a wine where they are competition admin
	 */
	public function testUpdateAllowedIfUserIsCompetitionAdmin() {
		$user = $this->getUserMock();
		$wine = Mockery::mock(Wine::class);
		$competition = Mockery::mock(Competition::class);
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->andReturn(false);
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(true);
		$wine->shouldReceive('getAttribute')
			->with('applicant')
			->andReturn($applicant);
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$association->shouldReceive('administrates')
			->andReturn(true);

		$allowed = $this->abilities->update($user, $wine, $data);

		$this->assertTrue($allowed);
	}

	public function testEnrollmentPdf() {
		$user = Mockery::mock(User::class);
		$wine = Mockery::mock(Wine::class);
		$wine->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->enrollmentPdf($user, $wine);

		$this->assertTrue($allowed);
	}

	public function testDelete() {
		$user = Mockery::mock(User::class);
		$wine = Mockery::mock(Wine::class);
		$wine->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->delete($user, $wine);

		$this->assertTrue($allowed);
	}

	public function testRedirect() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competition->shouldReceive('administrates')
			->once()
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->redirect($user, $competition);

		$this->assertTrue($allowed);
	}

	public function testImportKdb() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(false);

		$allowed = $this->abilities->importKdb($user, $competition);

		$this->assertFalse($allowed);
	}

	public function testImportSosi() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->importSosi($user, $competition);

		$this->assertTrue($allowed);
	}

	public function testImportExcluded() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competition->shouldReceive('administrates')
			->with($user)
			->andReturn(true);

		$allowed = $this->abilities->importExcluded($user, $competition);

		$this->assertTrue($allowed);
	}

}
