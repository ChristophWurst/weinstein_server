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

use App\MasterData\Competition;
use App\MasterData\User;
use Mockery;
use Mockery\MockInterface;

trait AbilitiesMock
{
    /**
     * @return User|MockInterface
     */
    public function getUserMock()
    {
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->andReturn(false);

        return $user;
    }

    /**
     * @return User|MockInterface
     */
    public function getAdminMock()
    {
        $admin = Mockery::mock(User::class);
        $admin->shouldReceive('isAdmin')->andReturn(true);

        return $admin;
    }

    /**
     * @return Competition|MockInterface
     */
    public function getCompetitionMock()
    {
        return Mockery::mock(Competition::class);
    }
}
