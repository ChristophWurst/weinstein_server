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

use Test\BrowserKitTestCase;

class WineSortsTest extends BrowserKitTestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testShowSorts() {
		$admin = factory(\App\MasterData\User::class, 'admin')->make();

		$this->be($admin);
		$this->get('settings/winesorts');
		$this->assertResponseOk();
		$this->see('Sorten');
	}

	public function testCreateWineSort() {
		$admin = factory(\App\MasterData\User::class, 'admin')->make();

		$this->be($admin);
		$this->get('settings/winesorts/create');
		$this->assertResponseOk();

		$this->post('settings/winesorts/create', [
			'order' => 'NaN',
			'name' => 'Veltline',
		]);
		$this->assertRedirectedTo('settings/winesorts/create');
		$this->get('settings/winesorts/create');
		$this->see('Fehler!');

		$data = [
			'order' => rand(1, 100000),
			'name' => str_random(10),
		];
		$this->post('settings/winesorts/create', $data);
		$this->assertRedirectedTo('settings/winesorts');
		$this->see($admin['order']);
		$this->see($admin['name']);
	}

}
