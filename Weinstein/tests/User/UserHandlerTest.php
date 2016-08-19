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

class UserHandlerTest extends TestCase {
    
    public function testGetUsersNoAdmin() {
        $user = new App\MasterData\User;
        $user->admin = false;
        
        $dataProvider = Mockery::mock('\Weinstein\User\UserDataProvider');
        $dataProvider->shouldReceive('getUsersForUser')
                ->with($user)
                ->once()
                ->andReturn('test');
        
        $service = new AppUserHandler($dataProvider);
        
        $this->assertSame('test', $service->getUsersUsers($user));
    }
    
    public function testGetUsersIsAdmin() {
        $user = new App\MasterData\User;
        $user->admin = true;
        
        $dataProvider = Mockery::mock('\Weinstein\User\UserDataProvider');
        $dataProvider->shouldReceive('getAllUsers')
                ->once()
                ->andReturn('test');
        
        $service = new App\MasterData\UserHandler($dataProvider);
        
        $this->assertSame('test', $service->getUsersUsers($user));
    }
    
    public function testNoAdmin() {
        $admin = new App\MasterData\User;
        $admin->username = 'userxx';
        $user = new App\MasterData\User;
        $user->username = 'useryy';
        $service = new App\MasterData\UserHandler(new App\MasterData\UserDataProvider());
        
        $this->assertFalse($service->isAdmin($admin, $user));
    }
    
    public function testIsAdmin() {
        $admin = new App\MasterData\User;
        $admin->username = 'userxx';
        $user = new App\MasterData\User;
        $user->username = 'userxx';
        $service = new App\MasterData\UserHandler(new App\MasterData\UserDataProvider());
        
        $this->assertTrue($service->isAdmin($admin, $user));
    }
    
}
