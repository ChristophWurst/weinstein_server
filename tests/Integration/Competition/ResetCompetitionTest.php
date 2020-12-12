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
 */

namespace Test\Integration\Competition;

use App\MasterData\Applicant;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\MasterData\WineSort;
use App\Wine;
use function factory;
use Test\BrowserKitTestCase;

class ResetCompetitionTest extends BrowserKitTestCase
{
    private function prepareCompetition(): Competition
    {
        $applicant = factory(Applicant::class)->create();
        $competition = factory(Competition::class)->create([
            'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
        ]);
        $winesort = factory(WineSort::class)->create();
        factory(Wine::class, 105)->create([
            'applicant_id' => $applicant->id,
            'competition_id' => $competition->id,
            'winesort_id' => $winesort->id,
        ]);

        return $competition;
    }

    public function testResetLargeDateSet()
    {
        $user = factory(User::class)->states('admin')->create();
        $competition = $this->prepareCompetition();
        $this->assertSame(105, $competition->wines()->count());

        $this->be($user);
        $this->get('competition/'.$competition->id.'/reset');
        $this->assertResponseOk();
        $this->post('competition/'.$competition->id.'/reset', [
            'reset' => 'Ja',
        ]);
        $this->assertRedirectedToRoute('settings.competitions');

        $this->assertSame(0, $competition->wines()->count());
    }
}
