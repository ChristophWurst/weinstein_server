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

class WineTest extends TestCase {

    use Way\Tests\ModelHelpers;
    
    /**
     * 
     * @param User $user
     */
    public function testNoAdmin() {
        $user = new User(array(
            'username' => 'test123',
            'admin' => false,
        ));
        $applicant = Mockery::mock('Applicant');
        $association = Mockery::mock('Association');
        $wine = Mockery::mock('Wine[getAttribute]');
        
        $wine->shouldReceive('getAttribute')
                ->with('applicant')
                ->once()
                ->andReturn($applicant);
        $wine->shouldReceive('getAttribute')
                ->with('association')
                ->once()
                ->andReturn($association);
        $applicant->shouldReceive('administrates')
                ->once()
                ->andReturn(false);
        $association->shouldReceive('administrates')
                ->once()
                ->andReturn(false);
        
        $this->assertSame(false, $wine->administrates($user));
    }
    
    /**
     * 
     * @param User $user
     */
    public function testApplicantAdmin() {
        $user = new User(array(
            'username' => 'test123',
            'admin' => false,
        ));
        $applicant = Mockery::mock('Applicant');
        $wine = Mockery::mock('Wine[getAttribute]');
        
        $wine->shouldReceive('getAttribute')
                ->with('applicant')
                ->once()
                ->andReturn($applicant);
        $applicant->shouldReceive('administrates')
                ->once()
                ->andReturn(true);
        
        $this->assertSame(true, $wine->administrates($user));
    }
    
    /**
     * 
     * @param User $user
     */
    public function testAssociationAdmin() {
        $user = new User(array(
            'username' => 'test123',
            'admin' => false,
        ));
        $applicant = Mockery::mock('Applicant');
        $association = Mockery::mock('Association');
        $wine = Mockery::mock('Wine[getAttribute]');
        
        $wine->shouldReceive('getAttribute')
                ->with('applicant')
                ->once()
                ->andReturn($applicant);
        $wine->shouldReceive('getAttribute')
                ->with('association')
                ->once()
                ->andReturn($association);
        $applicant->shouldReceive('administrates')
                ->once()
                ->andReturn(false);
        $association->shouldReceive('administrates')
                ->once()
                ->andReturn(true);
        
        $this->assertSame(true, $wine->administrates($user));
    }
    
    public function testAdminAdmin() {
        $user = new User(array(
            'admin' => true,
        ));
        $wine = new Wine();
        
        $this->assertSame(true, $wine->administrates($user));
    }

    public function testBelongsToApplicant() {
        $this->assertBelongsTo('applicant', 'Wine');
    }

    public function testBelognsToAssociation() {
        $this->assertBelongsTo('association', 'Wine');
    }

    public function testBelongsToCompetition() {
        $this->assertBelongsTo('competition', 'Wine');
    }

    public function testBelongsToWineQuality() {
        $this->assertBelongsTo('winequality', 'Wine');
    }

    public function testBelongsToWineSort() {
        $this->assertBelongsTo('winesort', 'Wine');
    }

    public function testHasManyTastingNubers() {
        $this->assertHasMany('tastingnumbers', 'Wine');
    }

}
