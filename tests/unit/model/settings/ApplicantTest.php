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
class ApplicantTest extends TestCase {
    
    use Way\Tests\ModelHelpers;
    
    public function testNoAdmin() {
        $user = new User(array(
            'username' => 'user123',
            'admin' => false,
        ));
        $applicant = Mockery::mock('Applicant[getAttribute]');
        $association = Mockery::mock('Association');
        $applicant->shouldReceive('getAttribute')
                ->with('wuser_username')
                ->once()
                ->andReturn('user5983');
        $applicant->shouldReceive('getAttribute')
                ->with('association')
                ->once()
                ->andReturn($association);
        $association->shouldReceive('administrates')
                ->with($user)
                ->once()
                ->andReturn(false);
        
        $this->assertFalse($applicant->administrates($user));
    }
    
    public function testApplicantAdmin() {
        $user = new User(array(
            'username' => 'test123',
            'admin' => false,
        ));
        $applicant = new Applicant(array(
            'wuser_username' => 'test123',
        ));
        
        $this->assertTrue($applicant->administrates($user));
    }
    
    public function testAssociationAdmin() {
        $user = new User(array(
            'username' => 'test123',
        ));
        $applicant = Mockery::mock('Applicant[getAttribute]');
        $association = Mockery::mock('Association');
        $applicant->shouldReceive('getAttribute')
                ->with('wuser_username')
                ->once()
                ->andReturn('bla');
        $applicant->shouldReceive('getAttribute')
                ->with('association')
                ->once()
                ->andReturn($association);
        $association->shouldReceive('administrates')
                ->with($user)
                ->once()
                ->andReturn(true);
        
        $this->assertTrue($applicant->administrates($user));
    }
    
    public function testAdminAdmin() {
        $user1 = new User(array(
            'username' => 'test123',
            'admin' => true
        ));
        $applicant = new Applicant();
        
        $this->assertTrue($applicant->administrates($user1));
    }
    
    public function testBelongsToAddress() {
        $this->assertBelongsTo('address', 'Applicant');
    }
    
    public function testBelongsToAssociation() {
        $this->assertBelongsTo('association', 'Applicant');
    }
    
    public function testBelognsToUser() {
        $this->assertBelongsTo('user', 'Applicant');
    }
    
    public function testHasManyWines() {
        $this->assertHasMany('wines', 'Applicant');
    }
    
}
