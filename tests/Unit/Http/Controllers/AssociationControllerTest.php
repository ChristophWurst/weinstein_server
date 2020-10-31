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
use App\Exceptions\ValidationException;
use App\Http\Controllers\AssociationController;
use App\MasterData\Association;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Laravel\BrowserKitTesting\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;
use Test\TestCase;

class AssociationControllerTest extends BrowserKitTestCase {

	/** @var MasterDataStore|MockInterface */
	private $masterDataStore;

	/** @var AuthManager|MockInterface */
	private $auth;

	/** @var Factory|MockInterface */
	private $view;

	/** @var AssociationController|MockInterface */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->masterDataStore = Mockery::mock(MasterDataStore::class);
		$this->auth = Mockery::mock(AuthManager::class);
		$this->view = Mockery::mock(Factory::class);

		$this->controller = Mockery::mock(AssociationController::class,
				[
				$this->masterDataStore,
				$this->auth,
				$this->view,
			])->makePartial();
		$this->controller->shouldReceive('authorize');
	}

	public function testIndex() {
		$user = Mockery::mock(User::class);
		$associations = new Collection();
		$view = Mockery::mock(View::class);

		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->masterDataStore->shouldReceive('getAssociations')
			->with($user)
			->once()
			->andReturn($associations);
		$this->view->shouldReceive('make')
			->once()
			->with('settings/association/index', [
				'associations' => $associations,
			])
			->andReturn($view);

		$this->assertSame($view, $this->controller->index());
	}

	public function testCreate() {
		$users = new Collection();
		$view = Mockery::mock(View::class);

		$this->masterDataStore->shouldReceive('getUsers')
			->once()
			->andReturn($users);
		$this->view->shouldReceive('make')
			->once()
			->with('settings/association/form', [
				'users' => ['none' => 'kein'],
			])
			->andReturn($view);

		$this->assertSame($view, $this->controller->create());
	}

	public function testStoreWithValidationException() {
		$request = Mockery::mock(Request::class);
		$request->shouldReceive('all')
			->once()
			->andReturn([
				'nr' => 123,
				'label' => 'Schrattenthal',
		]);
		$this->masterDataStore->shouldReceive('createAssociation')
			->once()
			->andThrow(new ValidationException());

		$this->response = TestResponse::fromBaseResponse($this->controller->store($request));

		$this->assertRedirectedToRoute('settings.associations/create', [], [
			'errors',
			'_old_input',
		]);
	}

	public function testStore() {
		$request = Mockery::mock(Request::class);
		$request->shouldReceive('all')
			->once()
			->andReturn([
				'nr' => 123,
				'label' => 'Schrattenthal',
				'wuser_username' => 'none',
		]);
		$this->masterDataStore->shouldReceive('createAssociation')
			->once()
			->with([
				'nr' => 123,
				'label' => 'Schrattenthal',
				// No username
		]);

		$this->response = TestResponse::fromBaseResponse($this->controller->store($request));

		$this->assertRedirectedToRoute('settings.associations');
	}

	public function testShow() {
		$association = Mockery::mock(Association::class);
		$view = Mockery::mock(View::class);
		$this->view->shouldReceive('make')
			->once()
			->with('settings/association/show', [
				'data' => $association,
			])
			->andReturn($view);

		$this->assertEquals($view, $this->controller->show($association));
	}

	public function testEdit() {
		$user = Mockery::mock(User::class);
		$association = Mockery::mock(Association::class);
		$users = new Collection();
		$view = Mockery::mock(View::class);

		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$user->shouldReceive('isAdmin')
			->once()
			->andReturn(true);
		$this->masterDataStore->shouldReceive('getUsers')
			->once()
			->andReturn($users);
		$this->view->shouldReceive('make')
			->once()
			->with('settings/association/form', [
				'data' => $association,
				'users' => ['none' => 'kein'],
			])
			->andReturn($view);

		$this->assertEquals($view, $this->controller->edit($association));
	}

	public function testUpdateValidationException() {
		$user = Mockery::mock(User::class);
		$association = Mockery::mock(Association::class);
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('all')
			->once()
			->andReturn([
				'nr' => 33,
				'wuser_username' => 'gerhard',
		]);
		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$user->shouldReceive('isAdmin')
			->once()
			->andReturn(false);
		$this->masterDataStore->shouldReceive('updateAssociation')
			->once()
			->with($association, [
				'nr' => 33,
			])
			->andThrow(new ValidationException());
		$association->shouldReceive('getAttribute')
			->with('id')
			->once()
			->andReturn(1234);

		$this->response = TestResponse::fromBaseResponse($this->controller->update($association, $request));
		$this->assertRedirectedToRoute('settings.associations/edit', [
			'association' => 1234,
			], [
			'errors',
			'_old_input',
		]);
	}
	
	public function testUpdate() {
		$association = Mockery::mock(Association::class);
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('all')
			->once()
			->andReturn([
				'nr' => 33,
				'wuser_username' => 'none',
		]);
		$this->masterDataStore->shouldReceive('updateAssociation')
			->once()
			->with($association, [
				'nr' => 33,
				'wuser_username' => null,
			]);

		$this->response = TestResponse::fromBaseResponse($this->controller->update($association, $request));
		$this->assertRedirectedToRoute('settings.associations');
	}

}
