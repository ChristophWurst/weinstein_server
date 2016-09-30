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

namespace Test\Unit\Tasting;

use App\Database\Repositories\CommissionRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\TasterRepository;
use App\Database\Repositories\TastingNumberRepository;
use App\Database\Repositories\TastingRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\Database\Repositories\WineRepository;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Tasting\Commission;
use App\Tasting\Handler;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use PHPUnit_Framework_MockObject_MockObject;
use Test\TestCase;

class HandlerTest extends TestCase {

	/** @var CommissionRepository|PHPUnit_Framework_MockObject_MockObject */
	private $commissionRepository;

	/** @var CompetitionRepository|PHPUnit_Framework_MockObject_MockObject */
	private $competitionRepository;

	/** @var TasterRepository|PHPUnit_Framework_MockObject_MockObject */
	private $tasterRepository;

	/** @var TastingRepository|PHPUnit_Framework_MockObject_MockObject */
	private $tastingRepository;

	/** @var TastingNumberRepository|PHPUnit_Framework_MockObject_MockObject */
	private $tastingNumberRepository;

	/** @var TastingSessionRepository|PHPUnit_Framework_MockObject_MockObject */
	private $tastingSessionRepository;

	/** @var WineRepository|PHPUnit_Framework_MockObject_MockObject */
	private $wineRepository;

	/** @var Handler */
	private $handler;

	protected function setUp() {
		parent::setUp();

		$this->commissionRepository = $this->getSimpleClassMock(CommissionRepository::class);
		$this->competitionRepository = $this->getSimpleClassMock(CompetitionRepository::class);
		$this->tasterRepository = $this->getSimpleClassMock(TasterRepository::class);
		$this->tastingRepository = $this->getSimpleClassMock(TastingRepository::class);
		$this->tastingNumberRepository = $this->getSimpleClassMock(TastingNumberRepository::class);
		$this->tastingSessionRepository = $this->getSimpleClassMock(TastingSessionRepository::class);
		$this->wineRepository = $this->getSimpleClassMock(WineRepository::class);

		$this->handler = new Handler($this->commissionRepository, $this->competitionRepository, $this->tasterRepository,
			$this->tastingRepository, $this->tastingNumberRepository, $this->tastingSessionRepository, $this->wineRepository);
	}

	public function testLockTastingNumbersInvalidTastingStage() {
		$this->setExpectedException(Exception::class, 'invalid competition state');

		$competition = \Mockery::mock(Competition::class);
		$competitionState = \Mockery::mock(CompetitionState::class);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->once()
			->with('description')
			->andReturn('ENROLLMENT');

		$this->handler->lockTastingNumbers($competition);
	}

	public function testLockTastingNumbers() {
		$competition = new Competition();
		$competition->competition_state_id = 2;
		$competitionState = new CompetitionState();
		$competitionState->description = 'TASTINGNUMBERS1';
		$competition->competitionstate()->associate($competitionState);

		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->handler->lockTastingNumbers($competition);
	}

	public function testLockTasting1() {
		$competition = new Competition();
		$competition->competition_state_id = 2;
		$competitionState = new CompetitionState();
		$competitionState->description = 'TASTING1';
		$competition->competitionstate()->associate($competitionState);

		$tastingSession = new TastingSession();
		$tastingSession->locked = false;
		$competition->tastingsessions->add($tastingSession);

		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->tastingSessionRepository->expects($this->once())
			->method('update')
			->with($tastingSession);

		$this->handler->lockTasting($competition);
	}

	public function testLockTasting2() {
		$competition = new Competition();
		$competition->competition_state_id = 2;
		$competitionState = new CompetitionState();
		$competitionState->description = 'TASTING2';
		$competition->competitionstate()->associate($competitionState);

		$tastingSession = new TastingSession();
		$tastingSession->locked = false;
		$competition->tastingsessions->add($tastingSession);

		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->tastingSessionRepository->expects($this->once())
			->method('update')
			->with($tastingSession);

		$this->handler->lockTasting($competition);
	}

	public function testLockTastingInvalidTastingStage() {
		$competition = new Competition();
		$competition->competition_state_id = 2;
		$competitionState = new CompetitionState();
		$competitionState->description = 'ENROLLMENT';
		$competition->competitionstate()->associate($competitionState);

		$this->setExpectedException(Exception::class);
		$this->handler->lockTasting($competition);
	}

	public function testLockKdb() {
		$competition = new Competition();
		$competition->competition_state_id = CompetitionState::STATE_KDB;
		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->handler->lockKdb($competition);
	}

	public function testLockExcluded() {
		$competition = new Competition();
		$competition->competition_state_id = CompetitionState::STATE_EXCLUDE;
		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->handler->lockKdb($competition);
	}

	public function testLockSosi() {
		$competition = new Competition();
		$competition->competition_state_id = CompetitionState::STATE_SOSI;
		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->handler->lockKdb($competition);
	}

	public function testLockChoosing() {
		$competition = new Competition();
		$competition->competition_state_id = CompetitionState::STATE_CHOOSE;
		$this->competitionRepository->expects($this->once())
			->method('update')
			->with($competition);

		$this->handler->lockKdb($competition);
	}

	public function testIsTastingFinished() {
		$competition = Mockery::mock(Competition::class);
		$tastingStage = new TastingStage();
		$competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, null)
			->will($this->returnValue(new Collection()));

		$this->assertTrue($this->handler->isTastingFinished($competition));
	}

	public function testIsTastingFinishedWithUntasted() {
		$competition = Mockery::mock(Competition::class);
		$tastingStage = new TastingStage();
		$competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, null)
			->will($this->returnValue(new Collection([
					new TastingNumber()
		])));

		$this->assertFalse($this->handler->isTastingFinished($competition));
	}

	public function testCreateTastingNumber() {
		// TODO: mock validator somehow
	}

	public function testImportTastingNumbers() {
		
	}

	public function testDeleteTastingNumber() {
		$tastingNumber = new TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('delete')
			->with($tastingNumber);

		$this->handler->deleteTastingNumber($tastingNumber);
	}

	public function testGetUntastedTastingNumbers() {
		$competition = new Competition();
		$tastingStage = new TastingStage();
		$tastingNumber = new TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, null)
			->will($this->returnValue([$tastingNumber]));

		$this->assertEquals([$tastingNumber], $this->handler->getUntastedTastingNumbers($competition, $tastingStage));
	}

	public function testGetAllTastingNumbers() {
		$competition = new Competition();
		$tastingStage = new TastingStage();
		$tastingNumber = new TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('findAllForCompetitionTastingStage')
			->with($competition, $tastingStage)
			->will($this->returnValue([$tastingNumber]));

		$this->assertEquals([$tastingNumber], $this->handler->getAllTastingNumbers($competition, $tastingStage));
	}

	public function testGetAllTastingSessionsAsAdmin() {
		$competition = new Competition();
		$tastingStage = new TastingStage();
		$user = Mockery::mock(User::class);
		$user->shouldReceive('isAdmin')->once()->andReturn(true);

		$this->tastingSessionRepository->expects($this->once())
			->method('findAll')
			->with($competition, $tastingStage)
			->will($this->returnValue([]));

		$this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, $user));
	}

	public function testGetAllTastingSessionsNoAdmin() {
		$competition = new Competition();
		$tastingStage = new TastingStage();

		$this->tastingSessionRepository->expects($this->once())
			->method('findAll')
			->with($competition, $tastingStage)
			->will($this->returnValue([]));

		$this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, null));
	}

	public function testGetAllTastingSessionsAsNonAdmin() {
		$competition = new Competition();
		$tastingStage = new TastingStage();
		$user = Mockery::mock(User::class);
		$user->shouldReceive('isAdmin')->once()->andReturn(false);

		$this->tastingSessionRepository->expects($this->once())
			->method('findForUser')
			->with($competition, $tastingStage, $user)
			->will($this->returnValue([]));

		$this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, $user));
	}

	public function testCreateTastingSession() {
		// TODO: bypass validator
	}

	public function testUpdateTastingSession() {
		// TODO: bypass validator
	}

	public function testLockTastingSession() {
		$tastingSession = new TastingSession();

		$this->tastingSessionRepository->expects($this->once())
			->method('update')
			->with($tastingSession, [
				'locked' => true,
		]);

		$this->handler->lockTastingSession($tastingSession);
	}

	public function testDeleteTastingSession() {
		$tastingSession = new TastingSession();

		$this->tastingSessionRepository->expects($this->once())
			->method('delete')
			->with($tastingSession);

		$this->handler->deleteTastingSession($tastingSession);
	}

	public function testAddTasterToTastingSession() {
		// TODO: bypass validator
	}

	public function testGetTastingSessionTasters() {
		$tastingSession = new TastingSession();

		$this->tasterRepository->expects($this->once())
			->method('findForTastingSession')
			->with($tastingSession);

		$this->handler->getCommissionTasters($tastingSession);
	}

	public function testCreateTasting() {
		// TODO: bypass validator
	}

	public function testUpdateTasting() {
		// TODO: bypass validator
	}

	public function testGetNextTastingNumbersAllTasted() {
		$tastingSession = new TastingSession();
		$competition = Mockery::mock(Competition::class);
		$tastingSession->competition = $competition;
		$tastingStage = new TastingStage();
		$competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, 2)
			->will($this->returnValue(new Collection()));

		$this->assertEquals([], $this->handler->getNextTastingNumbers($tastingSession));
	}

	public function testGetNextTastingNumbersOneLeft() {
		$tastingSession = new TastingSession();
		$competition = Mockery::mock(Competition::class);
		$tastingSession->competition = $competition;
		$tastingStage = new TastingStage();
		$competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);
		$tastingNumber1 = new TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, 2)
			->will($this->returnValue(new Collection([
					$tastingNumber1
		])));

		$expected = [
			'a' => $tastingNumber1,
		];
		$this->assertEquals($expected, $this->handler->getNextTastingNumbers($tastingSession));
	}

	public function testGetNextTastingNumbersTwoLeft() {
		$tastingSession = new TastingSession();
		$competition = Mockery::mock(Competition::class);
		$tastingSession->competition = $competition;
		$tastingStage = new TastingStage();
		$competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);
		$tastingNumber1 = new TastingNumber();
		$tastingNumber2 = new TastingNumber();
		// fake two commissions
		$tastingSession->commissions->add(new Commission(['id' => 1]));
		$tastingSession->commissions->add(new Commission(['id' => 2]));
		$x = $tastingSession->commissions->count();

		$this->tastingNumberRepository->expects($this->once())
			->method('findUntasted')
			->with($competition, $tastingStage, 2)
			->will($this->returnValue(new Collection([
					$tastingNumber1,
					$tastingNumber2,
		])));

		$expected = [
			'a' => $tastingNumber1,
			'b' => $tastingNumber2,
		];
		$this->assertEquals($expected, $this->handler->getNextTastingNumbers($tastingSession));
	}

	public function testIsTastingNumberTasted() {
		$tastingNumber = new TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('isTasted')
			->with($tastingNumber)
			->will($this->returnValue(false));

		$this->assertFalse($this->handler->isTastingNumberTasted($tastingNumber));
	}

}
