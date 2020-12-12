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

namespace Test\Unit\Auth\Abilities;

use App\Auth\Abilities\UserAbilities;
use Test\TestCase;

class UserAbilitiesTest extends TestCase
{
    use AbilitiesMock;

    /** @var UserAbilities */
    private $abilities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abilities = new UserAbilities();
    }

    public function testCreate()
    {
        $user = $this->getUserMock();

        $this->assertFalse($this->abilities->create($user));
    }

    public function testShow()
    {
        $user = $this->getUserMock();
        $toShow = $this->getUserMock();

        $toShow->shouldReceive('administrates')
            ->once()
            ->with($user)
            ->andReturn(true);

        $this->assertTrue($this->abilities->show($user, $toShow));
    }

    public function testEdit()
    {
        $user = $this->getUserMock();
        $toEdit = $this->getUserMock();

        $toEdit->shouldReceive('administrates')
            ->once()
            ->with($user)
            ->andReturn(true);

        $this->assertTrue($this->abilities->edit($user, $toEdit));
    }

    public function testDelete()
    {
        $user = $this->getUserMock();

        $this->assertFalse($this->abilities->delete($user));
    }
}
