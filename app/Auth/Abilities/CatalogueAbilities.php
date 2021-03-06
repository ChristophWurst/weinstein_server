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

namespace App\Auth\Abilities;

use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;

class CatalogueAbilities
{
    private function administratesCompetition(User $user, Competition $competition)
    {
        return $competition->administrates($user);
    }

    private function checkCompetitionState(Competition $competition)
    {
        return $competition->competition_state_id === CompetitionState::STATE_FINISHED;
    }

    public function create(User $user, Competition $competition)
    {
        return $this->administratesCompetition($user, $competition) && $this->checkCompetitionState($competition);
    }

    public function createTastingCatalogue(User $user, Competition $competition)
    {
        return $user->associations()->exists();
    }

    public function importNumbers(User $user, Competition $competition)
    {
        return $competition->administrates($user);
    }
}
