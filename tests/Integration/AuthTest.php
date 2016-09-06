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

namespace Test\Integration;

use App\MasterData\User;
use Test\TestCase;

class AuthTest extends TestCase {

	private $username = 'user1';
	private $password = 'user1!?';

	public function testLoginWrongCredentials() {
		$this->post('login', [
			'username' => 'user1',
			'password' => 'donotpassme',
		]);

		$this->assertRedirectedTo('login');
	}

	public function testLogin() {
		$this->post('login', [
			'username' => $this->username,
			'password' => $this->password,
		]);

		$this->assertRedirectedTo('account');
	}

	public function testLogoutAnonymously() {
		$this->get('logout');

		$this->assertRedirectedTo('login');
	}

	public function testLogout() {
		$user = User::find($this->username);
		$this->be($user);

		$this->get('logout');

		$this->assertRedirectedTo('');
	}

	public function testAccountPageAnonymously() {
		$this->get('account');

		$this->assertRedirectedTo('login');
	}

}
