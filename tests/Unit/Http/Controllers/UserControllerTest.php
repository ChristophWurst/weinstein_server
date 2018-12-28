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
use App\Http\Controllers\UserController;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class UserControllerTest extends BrowserKitTestCase {

	use AuthorizationHelper;

	/** @var Request|MockInterface */
	private $request;

	/** @var MasterDataStore|MockInterface */
	private $masterDataStore;

	/** @var AuthManager|MockInterface */
	private $authManager;

	/** @var Factory|MockInterface */
	private $viewFactory;

	/** @var UserController */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->request = Mockery::mock(Request::class);

		$this->masterDataStore = Mockery::mock(MasterDataStore::class);
		$this->authManager = Mockery::mock(AuthManager::class);
		$this->viewFactory = Mockery::mock(Factory::class);

		$this->controller = new UserController($this->masterDataStore, $this->authManager, $this->viewFactory);
	}

	public function testIndex() {
		$user = $this->getAdminMock();
		$users = new Collection([$user]);

		$this->be($user);
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->masterDataStore->shouldReceive('getUsers')
			->with($user)
			->andReturn($users);
		$viewData = [
			'users' => $users,
		];
		$this->viewFactory->shouldReceive('make')
			->with('settings/user/index', $viewData)
			->once()
			->andReturn('view');

		$this->assertEquals('view', $this->controller->index());
	}

	public function testCreate() {
		$user = $this->getAdminMock();
		$this->be($user);

		$this->viewFactory->shouldReceive('make')
			->with('settings/user/form')
			->andReturn('view');

		$this->assertEquals('view', $this->controller->create());
	}

	public function testStoreValidationException() {
		$user = $this->getAdminMock();
		$this->be($user);

		$this->request->shouldReceive('all')
			->once()
			->andReturn([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => 'true',
		]);
		$this->masterDataStore->shouldReceive('createUser')
			->with([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => true,
			])
			->once()
			->andThrow(new ValidationException());

		$this->response = $this->controller->store($this->request);

		$this->assertRedirectedToRoute('settings.users/create');
	}

	public function testStore() {
		$user = $this->getAdminMock();
		$newUser = Mockery::mock(User::class);
		$this->be($user);

		$this->request->shouldReceive('all')
			->once()
			->andReturn([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => 'true',
		]);
		$this->masterDataStore->shouldReceive('createUser')
			->with([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => true,
			])
			->once()
			->andReturn($newUser);

		$this->response = $this->controller->store($this->request);

		$this->assertRedirectedToRoute('settings.users');
	}

	public function testShow() {
		$user = $this->getAdminMock();
		$userToShow = Mockery::mock(User::class);

		$this->be($user);
		$this->viewFactory->shouldReceive('make')
			->once()
			->with('settings/user/show', [
				'data' => $userToShow,
			])
			->andReturn('view');

		$this->assertEquals('view', $this->controller->show($userToShow));
	}

	public function testEdit() {
		$user = $this->getAdminMock();
		$userToEdit = Mockery::mock(User::class);

		$this->be($user);
		$this->viewFactory->shouldReceive('make')
			->once()
			->with('settings/user/form', [
				'data' => $userToEdit,
			])
			->andReturn('view');

		$this->assertEquals('view', $this->controller->edit($userToEdit));
	}

	public function testUpdateValidationException() {
		$user = $this->getAdminMock();
		$userToEdit = Mockery::mock(User::class);

		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('jane');
		$userToEdit->shouldReceive('getAttribute')
			->with('username')
			->andReturn('garfield');
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->be($user);
		$this->request->shouldReceive('all')
			->once()
			->andReturn([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => 'true',
		]);
		$this->masterDataStore->shouldReceive('updateUser')
			->with($userToEdit, [
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => true,
			])
			->once()
			->andThrow(new ValidationException());

		$this->response = $this->controller->update($userToEdit, $this->request);

		$this->assertRedirectedToRoute('settings.users/edit', [
			'user' => 'garfield',
		]);
	}

	public function testUpdate() {
		$user = $this->getAdminMock();
		$userToEdit = Mockery::mock(User::class);

		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('jane');
		$userToEdit->shouldReceive('getAttribute')
			->with('username')
			->andReturn('garfield');
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->be($user);
		$this->request->shouldReceive('all')
			->once()
			->andReturn([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => 'true',
		]);
		$this->masterDataStore->shouldReceive('updateUser')
			->with($userToEdit, [
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => true,
			])
			->once();

		$this->response = $this->controller->update($userToEdit, $this->request);

		$this->assertRedirectedToRoute('settings.users');
	}

	public function testDelete() {
		$user = $this->getAdminMock();
		$userToDelete = Mockery::mock(User::class);

		$this->be($user);
		$this->viewFactory->shouldReceive('make')
			->once()
			->with('settings/user/delete', [
				'user' => $userToDelete,
			])
			->andReturn('view');

		$this->assertEquals('view', $this->controller->delete($userToDelete));
	}

	public function testDestroyDoNotDelete() {
		$user = $this->getAdminMock();
		$userToDelete = Mockery::mock(User::class);

		$this->be($user);
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$userToDelete->shouldReceive('is')
			->once()
			->with($user)
			->andReturn(false);
		$this->request->shouldReceive('get')
			->once()
			->with('del')
			->andReturn('Nein');
		$this->masterDataStore->shouldNotReceive('deleteUser');

		$this->response = $this->controller->destroy($userToDelete, $this->request);

		$this->assertRedirectedToRoute('settings.users');
	}

	public function testDestroySameUser() {
		$user = $this->getAdminMock();
		$userToDelete = $user;

		$this->be($user);
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$userToDelete->shouldReceive('is')
			->once()
			->with($user)
			->andReturn(true);
		$this->masterDataStore->shouldNotReceive('deleteUser');

		$this->expectException(\Exception::class);
		$this->controller->destroy($userToDelete, $this->request);
	}

	public function testDestroy() {
		$user = $this->getAdminMock();
		$userToDelete = Mockery::mock(User::class);

		$this->be($user);
		$this->authManager->shouldReceive('user')
			->once()
			->andReturn($user);
		$userToDelete->shouldReceive('is')
			->once()
			->with($user)
			->andReturn(false);
		$this->request->shouldReceive('get')
			->once()
			->with('del')
			->andReturn('Ja');
		$this->masterDataStore->shouldReceive('deleteUser')
			->once()
			->with($userToDelete);

		$this->response = $this->controller->destroy($userToDelete, $this->request);

		$this->assertRedirectedToRoute('settings.users');
	}

}
