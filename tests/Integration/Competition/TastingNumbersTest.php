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
use App\Tasting\TastingNumber;
use App\Wine;
use Test\TestCase;
use function factory;

class TastingNumbersTest extends TestCase {

	use \Illuminate\Foundation\Testing\DatabaseTransactions;

	public function testListTastingNumbers() {
		$user = factory(User::class, 'admin')->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		]);

		$this->be($user);

		$this->get('competition/' . $competition->id . '/numbers');
		$this->assertResponseOk();
	}

	public function testAssignTastingNumber() {
		$user = factory(User::class, 'admin')->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		]);
		$wine1 = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$wine2 = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$tastingNumber1 = factory(TastingNumber::class)->make([
			'wine_id' => $wine1->id,
		]);
		$tastingNumber2 = factory(TastingNumber::class)->make([
			'wine_id' => $wine1->id,
		]);

		$this->be($user);

		$this->get('competition/' . $competition->id . '/numbers/assign');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/numbers/assign',
			[
			'wine_nr' => $wine1->nr,
			'nr' => $tastingNumber1->nr,
		]);
		$this->assertRedirectedTo('competition/' . $competition->id . '/numbers/assign');

		$this->get('competition/' . $competition->id . '/numbers/assign');
		$this->assertResponseOk();
		$this->post('competition/' . $competition->id . '/numbers/assign',
			[
			'wine_nr' => $wine2->nr,
			'nr' => $tastingNumber2->nr,
		]);
		// Redirect to index now since all wines have been assigned
		$this->assertRedirectedTo('competition/' . $competition->id . '/numbers');
	}

	public function testDeallocateTastingNumbers() {
		$user = factory(User::class, 'admin')->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		]);
		$wine = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);
		$tastingNumber = factory(TastingNumber::class)->create([
			'wine_id' => $wine->id,
		]);

		$this->be($user);

		$this->get('number/' . $tastingNumber->id . '/deallocate');
		$this->assertResponseOk();
		$this->post('number/' . $tastingNumber->id . '/deallocate', [
			'del' => 'Nein',
		]);
		// Nothing should have changed
		$this->get('number/' . $tastingNumber->id . '/deallocate');
		$this->assertResponseOk();

		$this->get('number/' . $tastingNumber->id . '/deallocate');
		$this->assertResponseOk();
		$this->post('number/' . $tastingNumber->id . '/deallocate', [
			'del' => 'Ja',
		]);
		$this->assertRedirectedTo('competition/' . $competition->id . '/numbers');
	}

	public function testFinishAllocation() {
		$user = factory(User::class, 'admin')->create();
		$competition = factory(Competition::class)->create([
			'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
		]);
		$wine = factory(Wine::class)->create([
			'competition_id' => $competition->id,
		]);

		$this->be($user);

		$this->get('competition/' . $competition->id . '/numbers');
		$this->assertResponseOk();
		$this->dontSee('Zuweisung abschl');

		factory(TastingNumber::class)->create([
			'wine_id' => $wine->id,
			'tastingstage_id' => 1,
		]);

		$this->get('competition/' . $competition->id . '/numbers');
		$this->assertResponseOk();
		$this->see('Zuweisung abschl');

		$this->get('competition/' . $competition->id . '/complete-tastingnumbers/1');
		$this->assertResponseOk();
		$this->see('Zuweisung der 1. Kostnummern');

		$this->post('competition/' . $competition->id . '/complete-tastingnumbers/1', [
			'del' => 'Nein',
		]);
		$this->assertRedirectedTo('competition/' . $competition->id . '/numbers');

		$this->get('competition/' . $competition->id . '/complete-tastingnumbers/1');
		$this->assertResponseOk();
		$this->see('Zuweisung der 1. Kostnummern');

		$this->post('competition/' . $competition->id . '/complete-tastingnumbers/1', [
			'del' => 'Ja',
		]);
		$this->assertRedirectedTo('competition/' . $competition->id);
	}

}
