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

use App\MasterData\Applicant;
use App\MasterData\Competition;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Wine;
use App\WineQuality;
use Test\TestCase;
use function factory;

class EnrollmentTest extends TestCase {

	public function testFreshCompettionAsUser() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create();

		$this->be($user);
		$this->get('competition/' . $competition->id);
		$this->assertResponseOk();
		$this->see('Bewerb');
		$this->dontSee('0/0 Weinen übernommen');
	}

	public function testFreshCompettionAsAdmin() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'wuser_username' => $user->username,
		]);

		$this->be($user);
		$this->get('competition/' . $competition->id);
		$this->assertResponseOk();
		$this->see('Bewerb');
		$this->see('0/0 Weinen übernommen');
	}

	public function testAddWinesToACompetitionAsApplicantAdmin() {
		$user = factory(User::class)->create();
		$applicant = factory(Applicant::class)->create([
			'wuser_username' => $user->username,
		]);
		$competition = factory(Competition::class)->create();
		$wine = factory(Wine::class)->make([
			'applicant_id' => $applicant->id,
			'competition_id' => $competition->id,
		]);

		$this->be($user);
		$this->get('competition/' . $competition->id . '/wines');
		$this->assertResponseOk();

		$this->get('competition/' . $competition->id . '/wines/create');
		$this->assertResponseOk();
		$this->dontSee('Dateinummer');

		$this->post('competition/' . $competition->id . '/wines/create', $wine->toArray());
		$this->assertRedirectedTo('competition/' . $competition->id . '/wines/create');
		$this->get('competition/' . $competition->id . '/wines/create');
		$this->dontSee('Fehler!');
		$this->see('Wein gespeichert.');

		$this->get('competition/' . $competition->id . '/wines');
		$this->see($wine->label);
	}

}
