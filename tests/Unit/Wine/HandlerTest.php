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
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\Exceptions\WineLockedException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Validation\WineValidatorFactory;
use App\Wine;
use App\Wine\Handler;
use App\Wine\WineValidator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Test\TestCase;

class HandlerTest extends TestCase {

	/** @var WineRepository|\Mockery\MockInterface */
	private $wineRepository;

	/** @var WineValidatorFactory|\Mockery\MockInterface */
	private $validatorFactory;

	/** @var Handler */
	private $handler;

	protected function setUp() {
		parent::setUp();

		$this->wineRepository = Mockery::mock(WineRepository::class);
		$this->validatorFactory = Mockery::mock(WineValidatorFactory::class);

		$this->handler = new Handler($this->wineRepository, $this->validatorFactory);
	}

	public function testCreate() {
		// TODO: mock validators
	}

	public function testUpdate() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->once()
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
		$wine->shouldReceive('fill')
			->with($data);
		$wine->shouldReceive('isDirty')
			->andReturn(false);
		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, $data);

		$result = $this->handler->update($wine, $data);

		$this->assertEquals($wine, $result);
	}

	public function testUpdateWithValidationError() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once()
			->andThrow(new ValidationException());

		$this->setExpectedException(ValidationException::class);
		$this->handler->update($wine, $data);
	}

	public function testUpdateKdbWithWrongCompetitionState() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'kdb' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('getAttribute')
			->with('kdb')
			->once()
			->andReturn(false);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);

		$this->wineRepository->shouldReceive('update')
			->never()
			->with($wine, $data);

		$this->setExpectedException(InvalidCompetitionStateException::class);
		$this->handler->update($wine, $data);
	}

	public function testUpdateSosiWithWrongCompetitionState() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'sosi' => false,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('getAttribute')
			->with('sosi')
			->once()
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);

		$this->wineRepository->shouldReceive('update')
			->never()
			->with($wine, $data);

		$this->setExpectedException(InvalidCompetitionStateException::class);
		$this->handler->update($wine, $data);
	}

	public function testUpdateChosenWithWrongCompetitionState() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'chosen' => true,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('getAttribute')
			->with('chosen')
			->once()
			->andReturn(false);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);

		$this->wineRepository->shouldReceive('update')
			->never()
			->with($wine, $data);

		$this->setExpectedException(InvalidCompetitionStateException::class);
		$this->handler->update($wine, $data);
	}

	public function testUpdateExcludedWithWrongCompetitionState() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'excluded' => false,
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('getAttribute')
			->with('excluded')
			->once()
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);

		$this->wineRepository->shouldReceive('update')
			->never()
			->with($wine, $data);

		$this->setExpectedException(InvalidCompetitionStateException::class);
		$this->handler->update($wine, $data);
	}

	public function testUpdateAsNonAdminInLaterComptitionState() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'label' => 'test',
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('fill')
			->with($data);
		$wine->shouldReceive('isDirty')
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_CATALOGUE_NUMBERS);
		$user->shouldReceive('isAdmin')
			->andReturn(false);
		$this->wineRepository->shouldReceive('update')
			->never();
		$this->setExpectedException(WineLockedException::class);

		$this->handler->update($wine, $data);
	}

	public function testUpdateAsNonAdminInWhenNrIsAlreadySet() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'label' => 'test',
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$wine->shouldReceive('fill')
			->with($data);
		$wine->shouldReceive('isDirty')
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_ENROLLMENT);
		$wine->shouldReceive('getAttribute')
			->with('nr')
			->once()
			->andReturn(1234);
		$user->shouldReceive('isAdmin')
			->andReturn(false);
		$this->wineRepository->shouldReceive('update')
			->never();
		$this->setExpectedException(WineLockedException::class);

		$this->handler->update($wine, $data);
	}

	public function testUpdateAsAdminInWhenNrIsAlreadySet() {
		$user = Mockery::mock(User::class);
		$competition = Mockery::mock(Competition::class);
		$competitionState = Mockery::mock(CompetitionState::class);
		$wine = Mockery::mock(Wine::class);
		$data = [
			'label' => 'test',
		];
		$wine->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		Auth::shouldReceive('user')
			->andReturn($user);
		$validator = Mockery::mock(WineValidator::class);
		$this->validatorFactory->shouldReceive('newWineValidator')
			->once()
			->andReturn($validator);
		$validator->shouldReceive('setCompetition')
			->once()
			->with($competition);
		$validator->shouldReceive('setUser')
			->once()
			->with($user);
		$validator->shouldReceive('validateUpdate')
			->once();

		$competition->shouldReceive('getAttribute')
			->with('competitionState')
			->andReturn($competitionState);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_KDB);
		$wine->shouldReceive('fill')
			->with($data);
		$wine->shouldReceive('isDirty')
			->andReturn(true);
		$competitionState->shouldReceive('getAttribute')
			->with('id')
			->andReturn(CompetitionState::STATE_ENROLLMENT);
		$wine->shouldReceive('getAttribute')
			->with('nr')
			->once()
			->andReturn(1234);
		$user->shouldReceive('isAdmin')
			->andReturn(true);
		$this->wineRepository->shouldReceive('update')
			->once()
			->with($wine, $data);

		$result = $this->handler->update($wine, $data);

		$this->assertEquals($wine, $result);
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
