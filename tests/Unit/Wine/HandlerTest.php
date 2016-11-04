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

namespace Test\Unit\Wine;

use App\Database\Repositories\WineRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\User;
use App\Wine;
use App\Wine\Handler;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Test\TestCase;

class HandlerTest extends TestCase {

	/** @var WineRepository|\Mockery\MockInterface */
	private $wineRepository;

	/** @var Handler */
	private $handler;

	protected function setUp() {
		parent::setUp();

		$this->wineRepository = Mockery::mock(WineRepository::class);

		$this->handler = new Handler($this->wineRepository);
	}

	public function testCreate() {
		// TODO: mock validators
	}

	public function testUpdate() {
		// TODO: mock validators
	}

	public function testUpdateKdb() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => true,
		];

		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, [
				'kdb' => true,
		]);

		$this->handler->updateKdb($wine, $data);
	}

	public function testUpdateKdbValidationException() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => 'hello',
		];

		$this->wineRepository->shouldNotReceive('update');

		$this->setExpectedException(ValidationException::class);
		$this->handler->updateKdb($wine, $data);
	}

	public function testImportKdb() {
		// TODO
	}

	public function testUpdateExcluded() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => true,
		];

		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, [
				'excluded' => true,
		]);

		$this->handler->updateExcluded($wine, $data);
	}

	public function testUpdateExcludedValidationException() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => 'hello',
		];

		$this->wineRepository->shouldNotReceive('update');

		$this->setExpectedException(ValidationException::class);
		$this->handler->updateExcluded($wine, $data);
	}

	public function testImportExcluded() {
		// TODO
	}

	public function testUpdateSosi() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => true,
		];

		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, [
				'sosi' => true,
		]);

		$this->handler->updateSosi($wine, $data);
	}

	public function testUpdateSosiValidationException() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => 'hello',
		];

		$this->wineRepository->shouldNotReceive('update');

		$this->setExpectedException(ValidationException::class);
		$this->handler->updateSosi($wine, $data);
	}

	public function testImportSosi() {
		// TODO
	}

	public function testUpdateChosen() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => true,
		];

		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, [
				'chosen' => true,
		]);

		$this->handler->updateChosen($wine, $data);
	}

	public function testUpdateChosenValidationException() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'value' => 'hello',
		];

		$this->wineRepository->shouldNotReceive('update');

		$this->setExpectedException(ValidationException::class);
		$this->handler->updateChosen($wine, $data);
	}

	public function testImportChosen() {
		// TODO
	}

	public function testDelete() {
		$wine = Mockery::mock(Wine::class);

		$this->wineRepository->shouldReceive('delete')->once()->with($wine);

		$this->handler->delete($wine);
	}

	public function testGetUsersWineAsAdmin() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);

		$user->shouldReceive('isAdmin')->once()->andReturn(true);
		$result = Mockery::mock(Paginator::class);
		$this->wineRepository->shouldReceive('findAll')->once()->with($competition)->andReturn($result);

		$this->assertEquals($result, $this->handler->getUsersWines($user, $competition));
	}

	public function testGetUsersWineAsNonAdmin() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);

		$user->shouldReceive('isAdmin')->once()->andReturn(false);
		$result = Mockery::mock(Paginator::class);
		$this->wineRepository->shouldReceive('findUsersWines')->once()->with($user, $competition)->andReturn($result);

		$this->assertEquals($result, $this->handler->getUsersWines($user, $competition));
	}

}
