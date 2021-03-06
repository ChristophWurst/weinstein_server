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
use App\Wine;
use function factory;
use Test\BrowserKitTestCase;

class EnrollmentTest extends BrowserKitTestCase
{
    public function testFreshCompettionAsUser()
    {
        $user = factory(User::class)->create();
        $competition = factory(Competition::class)->create();

        $this->be($user);
        $this->get('competition/'.$competition->id);
        $this->assertResponseOk();
        $this->see('Bewerb');
        $this->dontSee('0/0 Weinen &uuml;bernommen');
    }

    public function testFreshCompetitionAsAdmin()
    {
        $user = factory(User::class)->create();
        $competition = factory(Competition::class)->create([
            'wuser_username' => $user->username,
        ]);

        $this->be($user);
        $this->get('competition/'.$competition->id);
        $this->assertResponseOk();
        $this->see('Bewerb');
        $this->see('0/0 Weinen &uuml;bernommen');
    }

    public function testAddWinesToACompetitionAsApplicantAdmin()
    {
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
        $this->get('competition/'.$competition->id.'/wines');
        $this->assertResponseOk();

        $this->get('competition/'.$competition->id.'/wines/create');
        $this->assertResponseOk();
        $this->dontSee('Dateinummer');

        $this->post('competition/'.$competition->id.'/wines/create', $wine->toArray());
        $this->assertRedirectedTo('competition/'.$competition->id.'/wines/create');
        $this->get('competition/'.$competition->id.'/wines/create');
        $this->dontSee('Fehler!');
        $this->see('Wein gespeichert.');
    }

    public function testShowWinesDetailPage()
    {
        $user = factory(User::class)->create();
        $applicant = factory(Applicant::class)->create([
            'wuser_username' => $user->username,
        ]);
        $competition = factory(Competition::class)->create([
            'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
        ]);
        $wine = factory(Wine::class)->create([
            'applicant_id' => $applicant->id,
            'competition_id' => $competition->id,
        ]);

        $this->be($user);

        // Go to wine's detail page
        $this->get('wines/'.$wine->id);
        $this->assertResponseOk();
        $this->see($wine->label);

        // Print enrollment PDF
        $this->get('wines/'.$wine->id.'/enrollment-pdf');
        $this->assertResponseOk();
    }

    public function testEditWineAsApplicantAdmin()
    {
        $this->markTestSkipped('needs investigation');

        $user = factory(User::class)->create();
        $applicant = factory(Applicant::class)->create([
            'wuser_username' => $user->username,
        ]);
        $competition = factory(Competition::class)->create([
            'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
        ]);
        $wine = factory(Wine::class)->create([
            'applicant_id' => $applicant->id,
            'competition_id' => $competition->id,
        ]);

        $this->be($user);

        // Go to wine's detail page
        $this->get('wines/'.$wine->id.'/edit');
        $this->assertResponseOk();
        $this->see($wine->label);
    }

    public function testEditWineAsAdmin()
    {
        $user = factory(User::class)->states('admin')->create();
        $applicant = factory(Applicant::class)->create([
            'wuser_username' => $user->username,
        ]);
        $competition = factory(Competition::class)->create([
            'competition_state_id' => CompetitionState::STATE_ENROLLMENT,
        ]);
        $wine = factory(Wine::class)->create([
            'applicant_id' => $applicant->id,
            'competition_id' => $competition->id,
        ]);

        $this->be($user);

        // Go to wine's detail page
        $this->get('wines/'.$wine->id.'/edit');
        $this->assertResponseOk();
        $this->see($wine->label);
    }
}
