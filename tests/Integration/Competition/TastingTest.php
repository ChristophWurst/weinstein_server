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
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Tasting\Commission;
use App\Tasting\Taster;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use App\Wine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Test\TestCase;
use function factory;

class TastingTest extends TestCase {

	use DatabaseTransactions;

	public function permissionData() {
		return [
				['session/{sessionId}/taste'],
				['session/{sessionId}/taste', 'POST'],
		];
	}

	/**
	 * @dataProvider permissionData
	 */
	public function testTastingPermissions($url, $method = 'GET') {
		$session = factory(TastingSession::class)->create();
		$user = factory(User::class)->make();

		$url = str_replace('{sessionId}', $session->id, $url);

		$this->be($user);
		$this->call($method, $url);
		$this->assertResponseStatus(403);
	}

	public function testSimpleTastingWorkFlow() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$session = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
			'tastingstage_id' => 1,
		]);
		$commission1 = factory(Commission::class)->create([
			'tastingsession_id' => $session->id,
			'side' => 'a',
		]);
		$commission2 = factory(Commission::class)->create([
			'tastingsession_id' => $session->id,
			'side' => 'b',
		]);
		$tasters1 = factory(Taster::class, 4)->create([
			'commission_id' => $commission1->id,
		]);
		$nr = 1;
		foreach ($tasters1 as $taster) {
			$taster->nr = $nr;
			$taster->save();
			$nr++;
		}
		$tasters2 = factory(Taster::class, 3)->create([
			'commission_id' => $commission2->id,
		]);
		$nr = 1;
		foreach ($tasters2 as $taster) {
			$taster->nr = $nr;
			$taster->save();
			$nr++;
		}
		$wine1 = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$wine2 = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$wine3 = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$tastingNumber1 = factory(TastingNumber::class)->create([
			'wine_id' => $wine1->id,
			'tastingstage_id' => 1,
		]);
		$tastingNumber2 = factory(TastingNumber::class)->create([
			'wine_id' => $wine2->id,
			'tastingstage_id' => 1,
		]);
		$tastingNumber3 = factory(TastingNumber::class)->create([
			'wine_id' => $wine3->id,
			'tastingstage_id' => 1,
		]);

		$this->be($user);
		$this->get('competition/' . $competition->id);
		$this->assertResponseOk();
		$this->get('competition/' . $competition->id . '/sessions');
		$this->assertResponseOk();
		$this->get('session/' . $session->id);
		$this->assertResponseOk();
		// Let's take a look at the statistics …
		$this->get('session/' . $session->id . '/statistics');
		$this->assertResponseOk();
		$this->get('session/' . $session->id . '/taste');
		$this->assertResponseOk();

		// Time to drink some wine!
		// First, we submit without any tasting data – whoops!
		$this->post('session/' . $session->id . '/taste',
			[
			'tastingnumber_id1' => $tastingNumber1->id,
			'comment-a' => '',
			'tastingnumber_id2' => $tastingNumber2->id,
			'comment-b' => 'ungenießbar!',
		]);
		$this->assertRedirectedTo('session/' . $session->id . '/taste');
		$this->get('session/' . $session->id . '/taste');
		$this->see('Fehler!');

		// Now with the actual data + comments
		$this->post('session/' . $session->id . '/taste',
			[
			'tastingnumber_id1' => $tastingNumber1->id,
			'a1' => 11,
			'a2' => 12,
			'a3' => 13,
			'a4' => '14',
			'comment-a' => '',
			'tastingnumber_id2' => $tastingNumber2->id,
			'b1' => '33',
			'b2' => '34',
			'b3' => 49,
			'comment-b' => 'ungenießbar!',
		]);
		$this->assertRedirectedTo('session/' . $session->id);
		$this->get('session/' . $session->id);
		$this->assertResponseOk();
		$this->dontSee('1. Verkostung abschließen');

		// Still one tasting number left …
		$this->post('session/' . $session->id . '/taste',
			[
			'tastingnumber_id1' => $tastingNumber3->id,
			'a1' => 21,
			'a2' => 22,
			'a3' => 23,
			'a4' => '24',
			'comment-a' => ''
		]);
		$this->assertRedirectedTo('session/' . $session->id);
		$this->get('session/' . $session->id);
		$this->assertResponseOk();
		$this->see('1. Verkostung abschließen');

		// Let's take a look at the statistics …
		$this->get('session/' . $session->id . '/statistics');
		$this->assertResponseOk();
	}

}
