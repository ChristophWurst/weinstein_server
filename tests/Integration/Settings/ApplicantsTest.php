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

namespace Test\Integration\Settings;

use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\User;
use function factory;
use function random_int;
use function str_random;
use Test\BrowserKitTestCase;

class ApplicantsTest extends BrowserKitTestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    public function createApplicant()
    {
        $admin = factory(User::class)->states('admin')->create();
        $association = factory(Association::class)->create();

        $this->be($admin);
        $this->get('settings/applicants');
        $this->assertResponseOk();

        $this->get('settings/applicants/create');
        $this->assertResponseOk();
        $this->post('settings/applicants/create', []);
        // No input, whoops.
        $this->assertRedirectedTo('settings/applicants/create');

        // But this time for real
        $id = random_int(10000, 99999);
        $this->post('settings/applicants/create',
            [
            'id' => $id,
            'association_id' => $association->id,
            'wuser_username' => 'none',
            'label' => str_random(10),
            'title' => 'Dr.',
            'firstname' => str_random(10),
            'lastname' => str_random(10),
            'phone' => random_int(10000, 100000000),
            'fax' => random_int(10000, 100000000),
            'mobile' => random_int(10000, 100000000),
            'email' => str_random(8) . '@' . str_random(5) . '.com',
            'web' => str_random(10) . '.com',
            'street' => str_random(20),
            'nr' => random_int(1, 300),
            'zipcode' => random_int(1000, 9999),
            'city' => str_random(10),
        ]);
        $this->assertRedirectedTo('settings/applicants');

        $this->seeInDatabase('applicants', [
            'id' => $id,
        ]);

        $this->get('settings/applicant/'.$id);
        $this->assertResponseOk();
    }

    public function testEditApplicant()
    {
        $admin = factory(User::class)->states('admin')->create();
        $applicant = factory(Applicant::class)->create();

        $this->be($admin);

        $this->get('settings/applicants/'.$applicant->id);
        $this->assertResponseOk();

        $this->get('settings/applicants/'.$applicant->id.'/edit');
        $this->assertResponseOk();

        $data = array_merge($applicant->address->toArray(), $applicant->toArray());
        $data['label'] = 'Winzerhof XYZ';
        $this->post('settings/applicants/'.$applicant->id.'/edit', $data);
        $this->assertRedirectedTo('settings/applicants');

        $this->get('settings/applicants');
        $this->see($data['label']);
    }
}
