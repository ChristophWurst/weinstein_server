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
use App\Tasting\Handler;
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

	public function testLockTastingNumbers() {
		
	}

	public function testLockTasting() {
		
	}

	public function testLockKdb() {
		
	}

	public function testLockExcluded() {
		
	}

	public function testLockSosi() {
		
	}

	public function testLockChoosing() {
		
	}

	public function testIsTastingFinished() {
		
	}

	public function testCreateTastingNumber() {
		
	}

	public function testImportTastingNumbers() {
		
	}

	public function testDeleteTastingNumber() {
		
	}

	public function testGetUntastedTastingNumbers() {
		
	}

	public function testGetAllTastingNumbers() {
		
	}

	public function testGetAllTastingSessions() {
		
	}

	public function testCreateTastingSession() {
		
	}

	public function testCreateCommission() {
		
	}

	public function testUpdateTastingSession() {
		
	}

	public function testLockTastingSession() {
		
	}

	public function testDeleteTastingSession() {
		
	}

	public function testAddTasterToTastingSession() {
		
	}

	public function testGetTastingSessionTasters() {
		
	}

	public function testCreateTasting() {
		
	}

	public function testUpdateTasting() {
		
	}

	public function testGetNextTastingNumbers() {
		
	}

	public function testIsTastingNumberTasted() {
		$tastingNumber = new \App\Tasting\TastingNumber();

		$this->tastingNumberRepository->expects($this->once())
			->method('isTasted')
			->with($tastingNumber)
			->will($this->returnValue(false));

		$this->assertFalse($this->handler->isTastingNumberTasted($tastingNumber));
	}

}
