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

use App\MasterData\Association;
use App\MasterData\User;
use Test\BrowserKitTestCase;
use function factory;
use function str_random;

class AssociationsTest extends BrowserKitTestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testCreateNewAssociation() {
		$admin = factory(User::class)->states('admin')->create();

		$this->be($admin);

		$this->get('settings/associations');
		$this->assertResponseOk();

		$this->get('settings/associations/create');
		$id = rand(10000, 20000);
		$this->post('settings/associations/create',
			[
			'id' => $id,
			'name' => str_random(10),
			'wuser_username' => '',
		]);
		$this->assertRedirectedTo('settings/associations');

		$this->get('settings/association/' . $id);
		$this->assertResponseOk();
	}

	public function testEditAssociation() {
		$admin = factory(User::class)->states('admin')->create();
		$association = factory(Association::class)->create();

		$this->be($admin);

		$this->get('settings/association/' . $association->id);
		$this->assertResponseOk();

		$this->get('settings/association/' . $association->id . '/edit');
		$this->assertResponseOk();

		$newId = 1 + (int) $association->id;
		$this->post('settings/association/' . $association->id . '/edit',
			[
			'id' => $newId,
			'name' => $association->name . '2',
			'wuser_username' => $admin->username,
		]);
		$this->assertRedirectedTo('settings/associations');

		// Check if we really applied the changes
		$this->get('settings/association/' . $newId);
		$this->see($association->id + 1);
		$this->see($association->label . '2');
		$this->see($admin->username);
	}

}
