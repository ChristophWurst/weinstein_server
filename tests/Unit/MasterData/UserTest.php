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

namespace Test\Unit\MasterData;

use App\MasterData\User;
use Illuminate\Support\Facades\Hash;
use Test\TestCase;

class UserTest extends TestCase
{
    public function testNoAdmin()
    {
        $user1 = new User([
            'username' => 'user1',
            'admin' => false,
        ]);
        $user2 = new User([
            'username' => 'user2',
        ]);

        $this->assertFalse($user2->administrates($user1));
    }

    public function testSameUserAdmin()
    {
        $user = new User([
            'username' => 'user123',
            'admin' => false,
        ]);
        $this->assertTrue($user->administrates($user));
    }

    public function testAdmin()
    {
        $admin = new User([
            'username' => 'admin123',
            'admin' => true,
        ]);
        $user = new User([
            'username' => 'user123',
        ]);

        $this->assertTrue($user->administrates($admin));
    }

    public function testSetHashedPassword()
    {
        Hash::shouldReceive('make')
            ->once()
            ->andReturn('hashedpwd');

        $testUser = new User();
        $testUser->password = 'test';

        $this->assertSame('hashedpwd', $testUser->password);
    }

    public function testRememberSedAndGetRememberToken()
    {
        $user = new User();
        $this->assertSame(null, $user->getRememberToken());
        $user->setRememberToken('test1234');
        $this->assertSame('test1234', $user->getRememberToken());
    }

    public function testGetRememberTokenName()
    {
        $user = new User();
        $this->assertSame('remember_token', $user->getRememberTokenName());
    }
}
