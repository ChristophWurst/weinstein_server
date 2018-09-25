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
use App\Tasting\TastingSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use function factory;
use Test\BrowserKitTestCase;

class TastingSessionTest extends BrowserKitTestCase {

	use DatabaseTransactions;

	public function anonymousPermissionsData() {
		return [
			['/competition/{competition_id}/sessions'],
			['/competition/{competition_id}/sessions/add'],
			['/competition/{competition_id}/sessions/add', 'POST'],
			['/session/{session_id}'],
			['/session/{session_id}/edit'],
			['/session/{session_id}/edit', 'POST'],
			['/session/{session_id}/statistics'],
		];
	}

	/**
	 * @dataProvider anonymousPermissionsData
	 */
	public function testAnonymousPermissions($url, $method = 'GET') {
		$competition = factory(Competition::class)->create();
		$session = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
		]);
		$url = str_replace('{competition_id}', $competition->id, $url);
		$url = str_replace('{session_id}', $session->id, $url);

		$this->call($method, $url);

		$this->assertRedirectedTo('/login');
	}

	public function nonSessionAdminPermissionsData() {
		return [
			['/competition/{competition_id}/sessions'],
			['/competition/{competition_id}/sessions/add'],
			['/competition/{competition_id}/sessions/add', 'POST'],
			['/session/{session_id}'],
			['/session/{session_id}/edit'],
			['/session/{session_id}/edit', 'POST'],
			['/session/{session_id}/statistics'],
		];
	}

	/**
	 * @dataProvider nonSessionAdminPermissionsData
	 */
	public function testNonSessionAdminPermissions($url, $method = 'GET') {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create();
		$session = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
		]);
		$url = str_replace('{competition_id}', $competition->id, $url);
		$url = str_replace('{session_id}', $session->id, $url);

		$this->be($user);
		$this->call($method, $url);

		$this->assertResponseStatus(403);
	}

	public function sessionAdminPermissionsData() {
		return [
			['/competition/{competition_id}/sessions'],
			['/competition/{competition_id}/sessions/add', false],
			['/session/{session_id}'],
			['/session/{session_id}/edit', false],
			['/session/{session_id}/statistics'],
		];
	}

	/**
	 * @dataProvider sessionAdminPermissionsData
	 */
	public function testSessionAdminPermissions($url, $allowed = true) {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$session = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$url = str_replace('{competition_id}', $competition->id, $url);
		$url = str_replace('{session_id}', $session->id, $url);

		$this->be($user);
		$this->get($url);

		if ($allowed) {
			$this->assertResponseOk();
		} else {
			$this->assertResponseStatus(403);
		}
	}

	public function testCreateTastingSessions() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
			'wuser_username' => $user->username,
		]);

		$this->be($user);
		$this->get('competition/' . $competition->id . '/sessions');
		$this->assertResponseOk();
		$this->get('competition/' . $competition->id . '/sessions/add');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/sessions/add',
			[
			'commissions' => 2,
			'wuser_username' => 'none',
		]);
		$session1 = TastingSession::where('competition_id', $competition->id)->first();
		$this->assertRedirectedTo('session/' . $session1->id);
		$this->get('session/' . $session1->id);
		$this->see('1. Sitzung');
		$this->see('Kommission A');
		$this->see('Kommission B');

		$this->get('competition/' . $competition->id . '/sessions');
		$this->assertResponseOk();
		$this->get('competition/' . $competition->id . '/sessions/add');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/sessions/add',
			[
			'commissions' => 1,
			'wuser_username' => 'none',
		]);
		$session2 = TastingSession::where('competition_id', $competition->id)->orderBy('id', 'desc')->first();
		$this->assertRedirectedTo('session/' . $session2->id);
		$this->get('session/' . $session2->id);
		$this->see('2. Sitzung');
		$this->see('Kommission A');
		$this->dontSee('Kommission B');
	}

	public function testEditTastingSession() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
			'wuser_username' => $user->username,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
		]);

		$this->be($user);
		$this->get('competition/' . $competition->id . '/sessions');
		$this->assertResponseOk();
		$this->get('session/' . $tastingSession->id);
		$this->assertResponseOk();
		$this->get('session/' . $tastingSession->id . '/edit');
		$this->assertResponseOk();

		// First, let's submit invalid values
		$this->post('session/' . $tastingSession->id . '/edit', [
			'wuser_username' => 'dontexist',
		]);
		$this->assertRedirectedTo('session/' . $tastingSession->id . '/edit');
		$this->get('session/' . $tastingSession->id . '/edit');
		$this->see('Fehler!');

		// Second, with valid data again
		$this->post('session/' . $tastingSession->id . '/edit', [
			'wuser_username' => 'none',
		]);
		$this->assertRedirectedTo('session/' . $tastingSession->id);
		$this->get('session/' . $tastingSession->id);
	}

}
