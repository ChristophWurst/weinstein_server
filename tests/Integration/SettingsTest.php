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
use function factory;

class SettingsTest extends TestCase {

	public function pagesThatRequireAuthData() {
		return [
			['settings'],
			['settings/competitions'],
			['settings/winesorts'],
			['settings/activitylog'],
			['settings/users'],
			['settings/applicants'],
			['settings/associations'],
		];
	}

	/**
	 * @dataProvider pagesThatRequireAuthData
	 */
	public function testAnonymouseAccessForbidden($uri, $method = 'GET') {
		$this->call($method, $uri);

		$this->assertRedirectedTo('login');
	}

	public function pagesThatNeedAdminPermsData() {
		return [
			['settings/users/create'],
			['settings/winesorts'],
			['settings/winesorts/create'],
			['settings/activitylog'],
			['settings/user/{username}/delete'],
			['settings/user/{username}/delete', 'POST'],
			['settings/user/{username}/edit'],
			['settings/user/{username}/edit', 'POST'],
			['settings/associations/create'],
			['settings/applicants/create'],
		];
	}

	/**
	 * @dataProvider pagesThatNeedAdminPermsData
	 */
	public function testNeedsAdminPermissions($uri, $method = 'GET') {
		$user = factory(User::class)->create();
		$this->be($user);

		$this->call($method, str_replace('{username}', $user->username, $uri));

		$this->assertResponseStatus(403);
	}

}
