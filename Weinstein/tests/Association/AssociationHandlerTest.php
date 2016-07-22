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

use Weinstein\Association\AssociationDataProvider;
use Weinstein\Association\AssociationHandler;

class AssociationHandlerTest extends TestCase {

    public function tearDown() {
        Mockery::close();
    }

    public function testDelete() {
        $ah = new AssociationHandler(new AssociationDataProvider());
        $association = Mockery::mock('Association');
        $association->shouldReceive('getAttribute')
                ->andReturn('test');
        $association->shouldReceive('delete')
                ->once();
        ActivityLogger::shouldReceive('log')
                ->once();
        $ah->delete($association);
    }
    
    public function testGetAllAssociationsAdmin() {
        $dataProvider = Mockery::mock('Weinstein\Association\AssociationDataProvider');
        $ah = new AssociationHandler($dataProvider);
        $user = new App\User;
        $user->admin = true;
        
        $dataProvider->shouldReceive('getAll')
                ->once()
                ->andReturn('resp');
        
        $this->assertSame('resp', $ah->getUsersAssociations($user));
    }
    
    public function testGetAllAssociationsNoAdmin() {
        $dataProvider = Mockery::mock('Weinstein\Association\AssociationDataProvider');
        $ah = new AssociationHandler($dataProvider);
        $user = new App\User;
        $user->admin = false;
        
        $dataProvider->shouldReceive('getAll')
                ->with($user)
                ->once()
                ->andReturn('resp');
        
        $this->assertSame('resp', $ah->getUsersAssociations($user));
    }

}
