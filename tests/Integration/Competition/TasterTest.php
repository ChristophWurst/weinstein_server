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
use App\Tasting\TastingSession;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\JsonResponse;
use Test\TestCase;
use function factory;

class TasterTest extends TestCase {

	use DatabaseTransactions;

	public function testListTasters() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);
		factory(Taster::class, 5)->create([
			'commission_id' => $commission->id,
		]);

		$this->be($user);
		$this->get('tasters?commission_id=' . $commission->id);
		$this->assertResponseOk();

		/** @var JsonResponse $resp */
		$resp = $this->response;
		$respContent = json_decode($resp->getContent());
		$this->assertCount(5, $respContent);
	}

	public function testListTastersWrongTastingStage() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_FINISHED,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);

		$this->be($user);
		$this->get('tasters?commission_id=' . $commission->id);
		$this->assertResponseStatus(403);
	}

	public function testListTastersNoPermissions() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_FINISHED,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);

		$this->be($user);
		$this->get('tasters?commission_id=' . $commission->id);
		$this->assertResponseStatus(403);
	}

	public function testListTastersWrongCommissionId() {
		$user = factory(User::class)->create();

		$this->be($user);
		$this->get('tasters?commission_id=100000000');
		$this->assertResponseStatus(400);
	}

	public function testCreateTaster() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);
		$taster = factory(Taster::class)->make([
			'commission_id' => $commission->id,
		]);

		$this->be($user);
		$this->post('tasters', $taster->jsonSerialize());
		$this->assertResponseOk();
		$this->assertJson($this->response->getContent());
	}

	public function testCreateTasterValidationError() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);

		$this->be($user);
		$this->post('tasters', [
			'commission_id' => $commission->id,
			'name' => '',
		]);
		$this->assertResponseStatus(422);
	}

	public function testCreateTasterInvalidCommission() {
		$user = factory(User::class)->create();

		$this->be($user);
		$this->post('tasters', [
			'commission_id' => 1000000,
			'name' => '',
		]);
		$this->assertResponseStatus(400);
	}

	public function testUpdateTaster() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);
		$taster = factory(Taster::class)->create([
			'commission_id' => $commission->id,
		]);

		$this->be($user);
		$this->put('tasters/' . $taster->id, [
			'name' => 'name2',
			'active' => false,
		]);
		$this->assertResponseOk();
		$this->assertJson($this->response->getContent());
		$this->seeInDatabase('taster', [
			'id' => $taster->id,
			'name' => 'name2',
			'active' => false,
		]);
	}

	public function testUpdateTasterValidationError() {
		$user = factory(User::class)->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_TASTING1,
		]);
		$tastingSession = factory(TastingSession::class)->create([
			'competition_id' => $competition->id,
			'wuser_username' => $user->username,
		]);
		$commission = factory(Commission::class)->create([
			'tastingsession_id' => $tastingSession->id,
		]);
		$taster = factory(Taster::class)->create([
			'commission_id' => $commission->id,
		]);

		$this->be($user);
		$this->put('tasters/' . $taster->id, [
			'commission_id' => $commission->id,
			'name' => '',
			'active' => 3,
		]);
		$this->assertResponseStatus(422);
	}

	public function testUpdateTasterInvalidCommission() {
		$user = factory(User::class)->create();

		$this->be($user);
		$this->post('tasters', [
			'commission_id' => 1000000,
			'name' => '',
		]);
		$this->assertResponseStatus(400);
	}

}
