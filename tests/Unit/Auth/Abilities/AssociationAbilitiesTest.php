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

use App\Auth\Abilities\AssociationAbilities;
use App\MasterData\Association;
use Mockery;
use Mockery\MockInterface;
use Test\TestCase;

class AssociationAbilitiesTest extends TestCase
{
    use AbilitiesMock;

    /** @var AssociationAbilities|MockInterface */
    private $abilities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abilities = new AssociationAbilities();
    }

    public function testShowEditAssociationAdmin()
    {
        $user = $this->getUserMock();
        $association = Mockery::mock(Association::class);

        $association->shouldReceive('getAttribute')
            ->with('user')
            ->andReturn($user);
        $user->shouldReceive('getAttribute')
            ->with('username')
            ->andReturn('gerda');

        $this->assertTrue($this->abilities->show($user, $association));
        $this->assertTrue($this->abilities->edit($user, $association));
    }

    public function testCreate()
    {
        $this->assertFalse($this->abilities->create($this->getUserMock()));
    }
}
