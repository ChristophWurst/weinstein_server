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

namespace App\MasterData;

use App\Validation\Validator;
use Illuminate\Database\Eloquent\Model;

class UserValidator extends Validator
{
    protected $modelClass = User::class;

    /**
     * Get attributes names.
     *
     * @return array
     */
    protected function getAttributeNames()
    {
        return [
            'username' => 'Benutzername',
            'password' => 'Passwort',
            'admin' => 'Administrator',
        ];
    }

    /**
     * Get rules for creating a new user.
     *
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data)
    {
        return [
            'username' => 'Required|Min:4|Max:80|alpha_dash|Unique:wuser',
            'password' => 'Min:5|Max:80',
        ];
    }

    /**
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null)
    {
        $usernameUnchanged = isset($data['username']) && $model->username === $data['username'];

        return [
            'username' => 'Required|Min:4|Max:80|alpha_dash'.($usernameUnchanged ? '' : '|Unique:wuser'),
            'password' => 'Min:5|Max:80',
        ];
    }
}
