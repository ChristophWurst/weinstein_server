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
class TastingSessionTest extends TestCase {

    use Way\Tests\ModelHelpers;
    
    public function testNoAdmin() {
        $user = new User(array(
            'username' => 'test123',
        ));
        $session = Mockery::mock('TastingSession[getAttribute]');
        $session->shouldReceive('getAttribute')
                ->with('user')
                ->once()
                ->andReturn(new User());
        $competition = Mockery::mock('Competition');
        $competition->shouldReceive('administrates')
                ->with($user)
                ->once()
                ->andReturn(false);
        $session->shouldReceive('getAttribute')
                ->with('competition')
                ->once()
                ->andReturn($competition);
        
        $this->assertFalse($session->administrates($user));
    }
    
    public function testSessionAdmin() {
        $user = new User(array(
            'username' => 'user123',
        ));
        $session = Mockery::mock('TastingSession[getAttribute]');
        $session->shouldReceive('getAttribute')
                ->with('user')
                ->once()
                ->andReturn($user);
        
        $this->assertTrue($session->administrates($user));
    }
    
    public function testCompetitionAdmin() {
        $user = new User(array(
            'username' => 'user123',
        ));
        $competition = Mockery::mock('Competition');
        $competition->shouldReceive('administrates')
                ->with($user)
                ->once()
                ->andReturn(true);
        $session = Mockery::mock('TastingSession[getAttribute]');
        $session->shouldReceive('getAttribute')
                ->with('user')
                ->once()
                ->andReturn(new User);
        $session->shouldReceive('getAttribute')
                ->with('competition')
                ->once()
                ->andReturn($competition);
        
        $this->assertTrue($session->administrates($user));
    }
    
    public function testAdmin() {
        $user = new User(array(
            'admin' => true,
        ));
        $session = new TastingSession();
        
        $this->assertTrue($session->administrates($user));
    }
    
    public function testHasManyCommissions() {
        $this->assertHasMany('commissions', 'TastingSession');
    }

    public function testBelongsToCompetition() {
        $this->assertBelongsTo('competition', 'TastingSession');
    }

    public function testBelongsToTastingStage() {
        $this->assertBelongsTo('tastingstage', 'TastingSession');
    }

    public function testBelongsToUser() {
        $this->assertBelongsTo('user', 'TastingSession');
    }

}
