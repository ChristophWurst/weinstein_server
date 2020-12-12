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

use App\MasterData\Competition;
use function factory;
use Test\BrowserKitTestCase;

class AuthorizationTest extends BrowserKitTestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    public static function urisThatNeedAuthenticationData()
    {
        return [
            ['competition/{id}'],
            ['competition/{id}/wines'],
            ['competition/{id}/wines/create'],
            ['competition/{id}/wines/create', 'POST'],
            ['competition/{id}/wines/export'],
            ['competition/{id}/wines/export-kdb'],
            ['competition/{id}/wines/export-sosi'],
            ['competition/{id}/wines/export-chosen'],
            ['competition/{id}/wines/redirect/123'],
        ];
    }

    /**
     * @dataProvider urisThatNeedAuthenticationData
     */
    public function testNoAnonymouseAccess($rawUri, $method = 'GET')
    {
        $competition = factory(Competition::class)->create();
        $uri = str_replace('{id}', $competition->id, $rawUri);

        $this->call($method, $uri);
        $this->assertRedirectedTo('login');
    }
}
