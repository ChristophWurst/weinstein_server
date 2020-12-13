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

namespace App\Tasting;

use App\MasterData\Competition;
use App\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

class TastingSessionValidator extends Validator
{
    /**
     * @var Competition
     */
    private $competition = null;

    protected $modelClass = TastingSession::class;

    /**
     * Get attributes names.
     *
     * @return array
     */
    protected function getAttributeNames()
    {
        return [
            'wuser_username' => 'Benutzer',
            'commissions' => 'Kommissionen',
        ];
    }

    /**
     * Get rules for creating a new tasting session.
     *
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data)
    {
        return [
            'wuser_username' => 'nullable|exists:wuser,username',
            'commissions' => 'required|integer|in:1,2',
        ];
    }

    /**
     * Get rules for updating an existing tasting session.
     *
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null)
    {
        return [
            'wuser_username' => 'nullable|exists:wuser,username',
        ];
    }

    /**
     * Set competition.
     *
     * @param Competition $competition
     */
    public function setCompetition(Competition $competition)
    {
        $this->competition = $competition;
    }
}
