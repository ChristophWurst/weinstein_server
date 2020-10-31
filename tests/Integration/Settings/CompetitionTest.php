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

use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use function factory;
use Test\BrowserKitTestCase;

class CompetitionTest extends BrowserKitTestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testShowCompetitions() {
		$admin = factory(User::class)->states('admin')->make();

		$this->be($admin);

		$this->get('settings/competitions');
		$this->assertResponseOk();
		$this->see('Bewerb');
	}

	public function testResetCompetition() {
		$admin = factory(User::class)->states('admin')->make();

		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_EXCLUDE,
		]);

		$this->be($admin);
		$this->get('competition/' . $competition->id . '/reset');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/reset', [
			'reset' => 'Nein',
		]);
		$this->assertRedirectedTo('settings/competitions');

		$this->get('competition/' . $competition->id . '/reset');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/reset', [
			'reset' => 'Ja',
		]);
		$this->assertRedirectedTo('settings/competitions');

		$this->seeInDatabase('competition',
			[
			'id' => $competition->id,
			'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		]);
	}

}
