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

use App\MasterData\Association;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Tasting\TastingStage;
use Illuminate\Support\Facades\Log;

class CompetitionAbilities
{
    use CommonAbilities;

    public function show(User $user, Competition $competition): bool
    {
        return $competition->administrates($user);
    }

    public function reset(User $user, Competition $competition): bool
    {
        return false; // Admin only
    }

    public function completeTasingNumbers(User $user, Competition $competition): bool
    {
        // TODO: what about the competition admin???
        if (! $competition->administrates($user)) {
            return false;
        }

        if ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS1) {
            $withNumber = $competition->wines()->withTastingNumber(TastingStage::find(1))->count();
            $total = $competition->wine_details()->count();
            if ($withNumber < $total) {
                return false;
            }

            return true;
        } elseif ($competition->competitionState->id === CompetitionState::STATE_TASTINGNUMBERS2) {
            // just allow it - there are no restrictions (for now)
            return true;
        } else {
            Log::error('invalid competition state in complete-tastingnumbers-filter');

            return false;
        }
    }

    public function completeTasting(User $user, Competition $competition): bool
    {
        if (! $competition->administrates($user)) {
            return false;
        }

        if ($competition->competitionState->id === CompetitionState::STATE_TASTING1) {
            $tasted = $competition->wine_details()->whereNotNull('rating1')->count();
            $total = $competition->wine_details()->count();
            if ($tasted < $total) {
                return false;
            }

            return true;
        } elseif ($competition->competitionState->id === CompetitionState::STATE_TASTING2) {
            // just allow it - there are no restrictions (for now)
            return true;
        } else {
            Log::error('invalid competition state in complete-tasting-filter');

            return false;
        }
    }

    public function completeKdb(User $user, Competition $competition): bool
    {
        return $competition->administrates($user) && $competition->competitionState->id === CompetitionState::STATE_KDB;
    }

    public function completeExcluded(User $user, Competition $competition): bool
    {
        return $competition->administrates($user) && $competition->competitionState->id === CompetitionState::STATE_EXCLUDE;
    }

    public function completeSosi(User $user, Competition $competition): bool
    {
        return $competition->administrates($user) && $competition->competitionState->id === CompetitionState::STATE_SOSI;
    }

    public function signChosen(User $user, Competition $competition, Association $association = null): bool
    {
        return $competition->competition_state_id === CompetitionState::STATE_CHOOSE // state
            && $user->associations()->exists() // is assoc admin? (for selection screen)
            && (is_null($association) ? true : $association->administrates($user)); // administrates?
    }

    public function completeChoosing(User $user, Competition $competition): bool
    {
        return $competition->administrates($user) && $competition->competitionState->id === CompetitionState::STATE_CHOOSE;
    }

    public function completeCatalogueNumbers(User $user, Competition $competition): bool
    {
        return $competition->administrates($user);
    }
}
