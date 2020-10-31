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

use App\Contracts\MasterDataStore;
use App\Contracts\TastingCatalogueHandler;
use App\Contracts\TastingHandler;
use App\Http\Controllers\CompetitionController;
use App\MasterData\Competition;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Laravel\BrowserKitTesting\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class CompetitionControllerTest extends BrowserKitTestCase {

	/** @var MasterDataStore|MockInterface */
	private $masterDataStore;

	/** @var TastingHandler|MockInterface */
	private $tastingHandler;

	/** @var TastingHandler|MockInterface */
	private $tastingCatalogueHandler;

	/** @var AuthManager|MockInterface */
	private $auth;

	/** @var Factory|MockInterface */
	private $view;

	/** @var CompetitionController|MockInterface */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->masterDataStore = Mockery::mock(MasterDataStore::class);
		$this->tastingHandler = Mockery::mock(TastingHandler::class);
		$this->tastingCatalogueHandler = Mockery::mock(TastingCatalogueHandler::class);
		$this->auth = Mockery::mock(AuthManager::class);
		$this->view = Mockery::mock(Factory::class);

		$this->controller = Mockery::mock(CompetitionController::class,
				[
				$this->masterDataStore,
				$this->tastingHandler,
				$this->tastingCatalogueHandler,
				$this->auth,
				$this->view
			])->makePartial();
		$this->controller->shouldReceive('authorize');
	}

	public function testIndex() {
		$user = Mockery::mock(User::class);
		$competitions = new Collection();
		$view = Mockery::mock(View::class);

		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->masterDataStore->shouldReceive('getCompetitions')
			->once()
			->andReturn($competitions);
		$this->view->shouldReceive('make')
			->once()
			->with('settings/competition/index', [
				'competitions' => $competitions,
			])
			->andReturn($view);

		$this->assertEquals($view, $this->controller->index());
	}

	public function testShow() {
		// TODO: move stuff to BL and mock where possible
	}

	public function testCompleteTastingWrongState() {
		$competition = Mockery::mock(Competition::class);
		$tasting = 3;

		$this->expectException(InvalidArgumentException::class);
		$this->controller->completeTasting($competition, $tasting);
	}

	public function testCompleteTasting() {
		$competition = Mockery::mock(Competition::class);
		$tasting = 2;
		$view = Mockery::mock(View::class);

		$this->view->shouldReceive('make')
			->once()
			->with('competition/complete-tasting', [
				'data' => $competition,
				'tasting' => $tasting,
			])
			->andReturn($view);

		$this->assertEquals($view, $this->controller->completeTasting($competition, $tasting));
	}

	public function testLockTastingWrongState() {
		$competition = Mockery::mock(Competition::class);
		$tasting = 3;
		$request = Mockery::mock(Request::class);

		$this->expectException(InvalidArgumentException::class);
		$this->controller->lockTasting($competition, $tasting, $request);
	}

	public function testLockTasting() {
		return; // TODO: fix test
		$competition = Mockery::mock(Competition::class);
		$tasting = 2;
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('has')
			->once()
			->with('del')
			->andReturn(true);
		$request->shouldReceive('get')
			->once()
			->with('del')
			->andReturn('Ja');
		$this->tastingHandler->shouldReceive('lockTasting')
			->once()
			->with($competition, $tasting);
		$competition->shouldReceive('getAttribute')
			->once()
			->with('id')
			->andReturn(12);

		// TODO: fix weird error "InvalidArgumentException: Route [competition/shows] not defined."
		$this->response = TestResponse::fromBaseResponse($this->controller->lockTasting($competition, $tasting, $request));

		$this->assertRedirectedToRoute('competition/show', [
			'competition' => 12,
		]);
	}

	public function testCompleteKdb() {
		$competition = Mockery::mock(Competition::class);
		$view = Mockery::mock(View::class);

		$this->view->shouldReceive('make')
			->once()
			->with('competition/complete-kdb', [
				'data' => $competition
			])
			->andReturn($view);

		$this->assertSame($view, $this->controller->completeKdb($competition));
	}

}
