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
		/* @var $otherUser User */
		$otherUser = factory(User::class)->create();
		$user = factory(User::class)->make();

		return [
			[$user, 'settings/users/create'],
			[$user, 'settings/winesorts'],
			[$user, 'settings/winesorts/create'],
			[$user, 'settings/activitylog'],
			[$user, 'settings/user/' . $user->username . '/delete'],
			[$user, 'settings/user/' . $user->username . '/delete', 'POST'],
			[$user, 'settings/user/' . $otherUser->username . '/edit'],
			[$user, 'settings/user/' . $otherUser->username . '/edit', 'POST'],
			[$user, 'settings/associations/create'],
			[$user, 'settings/applicants/create'],
		];
	}

	/**
	 * @dataProvider pagesThatNeedAdminPermsData
	 */
	public function testNeedsAdminPermissions(User $user, $uri, $method = 'GET') {
		$this->be($user);

		$this->call($method, $uri);

		$this->assertResponseStatus(403);
	}

}
