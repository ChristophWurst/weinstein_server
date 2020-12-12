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

use App\Auth\Abilities\TastingAbilities;
use App\Tasting\TastingSession;
use Mockery;
use Mockery\MockInterface;
use Test\TestCase;

class TastingAbilitiesTest extends TestCase
{
    use AbilitiesMock;

    /** @var TastingAbilities */
    private $abilities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->abilities = new TastingAbilities();
    }

    /**
     * @return MockInterface
     */
    private function getTastingSessionMock()
    {
        return Mockery::mock(TastingSession::class);
    }

    public function testCreateNonAdmin()
    {
        $user = $this->getUserMock();
        $tastingSession = $this->getTastingSessionMock();

        $tastingSession->shouldReceive('administrates')
            ->once()
            ->with($user)
            ->andReturn(false);

        $this->assertFalse($this->abilities->create($user, $tastingSession));
    }

    public function testCreateLockedSession()
    {
        $user = $this->getUserMock();
        $tastingSession = $this->getTastingSessionMock();

        $tastingSession->shouldReceive('administrates')
            ->once()
            ->with($user)
            ->andReturn(true);
        $tastingSession->shouldReceive('getAttribute')
            ->once()
            ->with('locked')
            ->andReturn(true);

        $this->assertFalse($this->abilities->create($user, $tastingSession));
    }
}
