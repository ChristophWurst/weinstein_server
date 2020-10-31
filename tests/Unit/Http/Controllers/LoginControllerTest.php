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

use App\Contracts\ActivityLogger;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthenticationController;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class LoginControllerTest extends BrowserKitTestCase {

	/** @var AuthManager|MockInterface */
	private $auth;

	/** @var ActivityLogger|MockInterface */
	private $activityLogger;

	/** @var Factory|MockInterface */
	private $view;

	/** @var AuthenticationController */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->auth = Mockery::mock(AuthManager::class);
		$this->activityLogger = Mockery::mock(ActivityLogger::class);
		$this->view = Mockery::mock(Factory::class);

		$this->controller = new LoginController($this->auth, $this->activityLogger, $this->view);
	}

	public function testAccount() {
		$view = Mockery::mock(View::class);

		$this->view->shouldReceive('make')
			->with('account/account')
			->once()
			->andReturn($view);

		$this->assertEquals($view, $this->controller->account());
	}

	public function testLogin() {
		$view = Mockery::mock(View::class);

		$this->view->shouldReceive('make')
			->with('account/login')
			->once()
			->andReturn($view);

		$this->assertEquals($view, $this->controller->login());
	}

	public function testAuthFailedLogin() {
		$request = Mockery::mock(Request::class);

		$request->shouldReceive('input')
			->once()
			->with('username')
			->andReturn('jane');
		$request->shouldReceive('input')
			->once()
			->with('password')
			->andReturn('passme');
		$this->auth->shouldReceive('attempt')
			->once()
			->with([
				'username' => 'jane',
				'password' => 'passme',
				], true)
			->andReturn(false);

		$this->response = $this->controller->auth($request);

		$this->assertRedirectedToRoute('login', [], [
			'_old_input'
		]);
	}

	public function testAuth() {
		$request = Mockery::mock(Request::class);
		$user = Mockery::mock(User::class);

		$request->shouldReceive('input')
			->once()
			->with('username')
			->andReturn('jane');
		$request->shouldReceive('input')
			->once()
			->with('password')
			->andReturn('passme');
		$this->auth->shouldReceive('attempt')
			->once()
			->with([
				'username' => 'jane',
				'password' => 'passme',
				], true)
			->andReturn(true);
		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$user->shouldReceive('getAttribute')
			->with('first_login')
			->once()
			->andReturn(true);
		$user->shouldReceive('setAttribute')
			->with('first_login', false)
			->once();
		$user->shouldReceive('save')
			->once();
		$this->activityLogger->shouldReceive('logUserAction')
			->once();

		$this->response = $this->controller->auth($request);

		$this->assertRedirectedToRoute('account');
	}

	public function testLogout() {
		$user = Mockery::mock(User::class);

		$this->auth->shouldReceive('check')
			->once()
			->andReturn(true);
		$this->auth->shouldReceive('user')
			->once()
			->andReturn($user);
		$this->auth->shouldReceive('logout')
			->once();
		$this->activityLogger->shouldReceive('logUserAction')
			->once();

		$this->response = $this->controller->logout();

		$this->assertRedirectedToRoute('start');
	}

}
