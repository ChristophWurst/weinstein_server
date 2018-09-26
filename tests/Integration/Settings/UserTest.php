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

namespace Test\Integration\Settings;

use App\MasterData\User;
use Test\BrowserKitTestCase;
use function factory;

class UserTest extends BrowserKitTestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testSimpleUserWorkFlow() {
		$user = factory(User::class)->create([
			'password' => 'passme',
		]);

		// Let's log in and go to our account page
		$this->post('login', [
			'username' => $user->username,
			'password' => 'passme',
		]);
		$this->get('settings/user/' . $user->username);
		$this->assertResponseOk();

		// Ok, logged in. Now let's change the password
		$this->post('settings/user/' . $user->username . '/edit',
			[
			'username' => $user->username,
			'password' => 'passme2',
		]);
		$this->assertRedirectedTo('settings/users');

		// Good. Now let's see if we can still log in
		$this->get('logout');
		$this->post('login', [
			'username' => $user->username,
			'password' => 'passme2',
		]);
		$this->assertRedirectedTo('account');
	}

	public function testSimpleAdminWorkFlow() {
		$user = factory(User::class)->create([
			'password' => 'passme',
		]);
		$admin = factory(User::class, 'admin')->create();

		$this->be($admin);

		$this->get('settings/user/' . $user->username);
		$this->assertResponseOk();

		// Let's change the user's username
		$this->post('settings/user/' . $user->username . '/edit',
			[
			'username' => $user->username . '2',
			'password' => '',
		]);
		$this->assertRedirectedTo('settings/users');

		$this->get('logout');
		$this->assertRedirectedTo('');

		$this->post('login', [
			'username' => $user->username . '2',
			'password' => 'passme',
		]);
		$this->assertRedirectedTo('account');

		// So far, so good. Now let's delete that user
		$this->be($admin);

		$this->get('settings/user/' . $user->username . '2/delete');
		$this->assertResponseOk();
		$this->post('settings/user/' . $user->username . '2/delete', [
			'del' => 'Ja',
		]);
		$this->assertRedirectedTo('settings/users');
		
		$this->get('settings/user/' . $user->username . '2');
		$this->assertResponseStatus(404);
	}

}
