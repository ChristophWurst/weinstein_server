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

namespace Test\Unit\Http\Controllers;

use App\Contracts\TastingHandler;
use App\Exceptions\ValidationException;
use App\Http\Controllers\TastingController;
use App\MasterData\Competition;
use App\Tasting\Commission;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\BrowserKitTesting\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class TastingControllerTest extends BrowserKitTestCase {

	/** @var TastingHandler|MockInterface */
	private $tastingHandler;

	/** @var Factory|MockInterface */
	private $view;

	/** @var TastingController|MockInterface */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->tastingHandler = Mockery::mock(TastingHandler::class);
		$this->view = Mockery::mock(Factory::class);

		$this->controller = Mockery::mock(TastingController::class, [
				$this->tastingHandler,
				$this->view,
			])->makePartial();
		$this->controller->shouldReceive('authorize');
	}

	public function testAdd() {
		$tastingSession = Mockery::mock(TastingSession::class);
		$competition = Mockery::mock(Competition::class);
		$tastingNumbers = new Collection();
		$view = Mockery::mock(View::class);

		$tastingSession->shouldReceive('getAttribute')
			->with('competition')
			->andReturn($competition);
		$this->tastingHandler->shouldReceive('getNextTastingNumbers')
			->once()
			->with($tastingSession)
			->andReturn($tastingNumbers);
		$this->view->shouldReceive('make')
			->once()
			->with('competition/tasting/tasting-session/tasting/form',
				[
				'competition' => $competition,
				'tastingSession' => $tastingSession,
				'tastingNumbers' => $tastingNumbers,
			])
			->andReturn($view);

		$this->assertSame($view, $this->controller->add($tastingSession));
	}

	public function testStoreWithValidationException() {
		$tastingSession = Mockery::mock(TastingSession::class);
		$request = Mockery::mock(Request::class);
		$data = [
			'tastingnumber_id1' => '12',
			'a1' => '12',
			'a2' => '14',
		];

		$request->shouldReceive('all')
			->once()
			->andReturn($data);
		$this->tastingHandler->shouldReceive('createTasting')
			->once()
			->with($data, $tastingSession)
			->andThrow(new ValidationException());
		$tastingSession->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(33);

		$this->response = TestResponse::fromBaseResponse($this->controller->store($tastingSession, $request));

		$this->assertRedirectedToRoute('tasting.session/taste', [
			'tastingsession' => 33,
			], [
			'errors',
			'_old_input',
		]);
	}

	public function testStore() {
		$tastingSession = Mockery::mock(TastingSession::class);
		$request = Mockery::mock(Request::class);
		$data = [
			'tastingnumber_id1' => '12',
			'a1' => '12',
			'a2' => '14',
		];

		$request->shouldReceive('all')
			->once()
			->andReturn($data);
		$this->tastingHandler->shouldReceive('createTasting')
			->once()
			->with($data, $tastingSession);
		$tastingSession->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(33);

		$this->response = TestResponse::fromBaseResponse($this->controller->store($tastingSession, $request));

		$this->assertRedirectedToRoute('tasting.session/show', [
			'tastingsession' => 33,
		]);
	}

	public function testEdit() {
		$tastingSession = Mockery::mock(TastingSession::class);
		$competition = Mockery::mock(Competition::class);
		$tastingNumber = Mockery::mock(TastingNumber::class);
		$commission = Mockery::mock(Commission::class);
		$view = Mockery::mock(View::class);

		$this->tastingHandler->shouldReceive('isTastingNumberTasted')
			->once()
			->with($tastingNumber)
			->andReturn(true);
		$tastingSession->shouldReceive('getAttribute')
			->once()
			->andReturn($competition);
		$this->view->shouldReceive('make')
			->once()
			->with('competition/tasting/tasting-session/tasting/form',
				[
				'edit' => true,
				'competition' => $competition,
				'commission' => $commission,
				'tastingnumber' => $tastingNumber,
			])
			->andReturn($view);

		$this->assertEquals($view, $this->controller->edit($tastingSession, $tastingNumber, $commission));
	}

	public function testUpdate() {
		$tastingSession = Mockery::mock(TastingSession::class);
		$tastingNumber = Mockery::mock(TastingNumber::class);
		$commission = Mockery::mock(Commission::class);
		$request = Mockery::mock(Request::class);
		$data = [
			'tastingnumber_id1' => '145',
			'a1' => '33',
			'a2' => '25',
			'b1' => '13',
		];

		$request->shouldReceive('all')
			->once()
			->andReturn($data);
		$this->tastingHandler->shouldReceive('updateTasting')
			->once()
			->with($data, $tastingNumber, $tastingSession, $commission);
		$tastingSession->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(7);

		$this->response = TestResponse::fromBaseResponse($this->controller->update($tastingSession, $tastingNumber, $commission, $request));

		$this->assertRedirectedToRoute('tasting.session/show', [
			'tastingsession' => 7,
		]);
	}

}
