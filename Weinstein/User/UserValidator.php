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

namespace Weinstein\User;

use Illuminate\Database\Eloquent\Model;
use Weinstein\Support\Validation\Validator;

class UserValidator extends Validator {
    
    /**
     * Models class name
     * 
     * @var string
     */
    protected $modelClass = 'User';
    
    /**
     * Get attributes names
     * 
     * @return array
     */
    protected function getAttributeNames() {
        return array(
            'username' => 'Benutzername',
            'password' => 'Passwort',
            'admin' => 'Administrator'
        );
    }
    
    /**
     * Get rules for creating a new user
     * 
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data) {
        return array(
            'username' => 'Required|Min:5|Max:80|alpha_dash|Unique:wuser',
            'password' => 'Min:5|Max:80'
        );
    }
    
    /**
     * 
     * @param array $data
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null) {
        $usernameUnchanged = isset($data['username']) && $model->username === $data['username'];
        return array(
            'username' => 'Required|Min:5|Max:80|alpha_dash' . ($usernameUnchanged ? '' : '|Unique:wuser'),
            'password' => 'Min:5|Max:80'
        );
    }
    
}
