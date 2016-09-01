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
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Test\TestCase;

class UserControllerTest extends TestCase {

	/** @var MasterDataStore|\Mockery\MockInterface */
	private $masterDataStore;

	/** @var AuthManager|\Mockery\MockInterface */
	private $authManager;

	use AuthorizationHelper;

	protected function setUp() {
		parent::setUp();

		$this->masterDataStore = Mockery::mock(MasterDataStore::class);
		$this->authManager = Mockery::mock(AuthManager::class);

		$this->app->instance(MasterDataStore::class, $this->masterDataStore);
		$this->app->instance(AuthManager::class, $this->authManager);
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

		$this->get('settings/users');

		$this->assertResponseOk();
		$this->assertViewHas('users', $users);
	}

	public function testCreate() {
		$user = $this->getAdminMock();
		$this->be($user);

		$this->get('settings/users/create');

		$this->assertResponseOk();
	}

	public function testStore() {
		$user = $this->getAdminMock();
		$newUser = Mockery::mock(User::class);
		$this->be($user);
		$this->masterDataStore->shouldReceive('createUser')
			->with([
				'username' => 'garfield',
				'password' => 'lasagne',
				'admin' => true,
			])
			->once()
			->andReturn($newUser);

		$this->post('settings/users/create',
			[
			'username' => 'garfield',
			'password' => 'lasagne',
			'admin' => 'true',
		]);

		//$this->assertResponseOk();
	}

}
