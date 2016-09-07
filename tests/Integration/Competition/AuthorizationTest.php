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

namespace Test\Integration\Competition;

use App\MasterData\Competition;
use Test\TestCase;
use function factory;

class AuthorizationTest extends TestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function urisThatNeedAuthenticationData() {
		$competition = factory(Competition::class)->create();

		return [
			['competition/' . $competition->id],
			['competition/' . $competition->id . '/wines'],
			['competition/' . $competition->id . '/wines/create'],
			['competition/' . $competition->id . '/wines/create', 'POST'],
			['competition/' . $competition->id . '/wines/export'],
			['competition/' . $competition->id . '/wines/export-kdb'],
			['competition/' . $competition->id . '/wines/export-spsi'],
			['competition/' . $competition->id . '/wines/export-chosen'],
			['competition/' . $competition->id . '/wines/redirect/123'],
		];
	}

	/**
	 * @dataProvider urisThatNeedAuthenticationData
	 */
	public function testNoAnonymouseAccess($uri, $method = 'GET') {
		$this->call($method, $uri);
		$this->assertRedirectedTo('login');
	}

}
