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

use App\Auth\Abilities\EvaluationAbilities;
use Test\TestCase;

class EvaluationAbilitiesTest extends TestCase
{
    use AbilitiesMock;

    /** @var EvaluationAbilities */
    private $abilities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abilities = new EvaluationAbilities();
    }

    public function testShowAsAdmin()
    {
        $user = $this->getAdminMock();
        $competition = $this->getCompetitionMock();

        $this->assertTrue($this->abilities->show($user, $competition));
    }

    public function testShowNonCompetitionAdmin()
    {
        $user = $this->getUserMock();
        $competition = $this->getCompetitionMock();
        $competitionUser = $this->getUserMock();

        $competition->shouldReceive('getAttribute')
            ->once()
            ->with('user')
            ->andReturn($competitionUser);
        $competitionUser->shouldReceive('getAttribute')
            ->once()
            ->with('username')
            ->andReturn('susan');
        $user->shouldReceive('getAttribute')
            ->once()
            ->with('username')
            ->andReturn('franz');

        $this->assertFalse($this->abilities->show($user, $competition));
    }

    public function testShowCompetitionAdmin()
    {
        $user = $this->getUserMock();
        $competition = $this->getCompetitionMock();
        $competitionUser = $this->getUserMock();

        $competition->shouldReceive('getAttribute')
            ->once()
            ->with('user')
            ->andReturn($competitionUser);
        $competitionUser->shouldReceive('getAttribute')
            ->once()
            ->with('username')
            ->andReturn('susan');
        $user->shouldReceive('getAttribute')
            ->once()
            ->with('username')
            ->andReturn('susan');

        $this->assertTrue($this->abilities->show($user, $competition));
    }
}
