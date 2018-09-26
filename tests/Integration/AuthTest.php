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
use Test\BrowserKitTestCase;

class AuthTest extends BrowserKitTestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testLoginWrongCredentials() {
		$this->post('login', [
			'username' => 'user1',
			'password' => 'donotpassme',
		]);

		$this->assertRedirectedTo('login');
	}

	public function testLogin() {
		$user = factory(User::class)->create([
			'password' => 'passme', // The mutator will hash it for us
		]);

		$this->post('login', [
			'username' => $user->username,
			'password' => 'passme',
		]);

		$this->assertRedirectedTo('account');
	}

	public function testLogoutAnonymously() {
		$this->get('logout');

		$this->assertRedirectedTo('login');
	}

	public function testLogout() {
		$user = factory(User::class)->make();
		$this->be($user);

		$this->get('logout');

		$this->assertRedirectedTo('');
	}

	public function testAccountPageAnonymously() {
		$this->get('account');

		$this->assertRedirectedTo('login');
	}

}
