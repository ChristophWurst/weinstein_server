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

class AssociationValidator extends Validator
{
    /**
     * Models class name.
     *
     * @var string
     */
    protected $modelClass = Association::class;

    /**
     * Get attribute names.
     *
     * @return array
     */
    protected function getAttributeNames()
    {
        return [
            'id' => 'Standnummer',
            'name' => 'Bezeichnung',
            'email' => 'E-Mail',
            'wuser_username' => 'Benutzer',
        ];
    }

    /**
     * Get rules for creating a new association.
     *
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data)
    {
        return [
            'id' => 'Required|integer|min:1|unique:association,id',
            'name' => 'Required|between:4,80|unique:association,name',
            'email' => 'email',
            'wuser_username' => 'Exists:wuser,username',
        ];
    }

    /**
     * Get rules for updating an existing association.
     *
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null)
    {
        //only check uniqueness of id if it was changed
        $idUnchanged = isset($data['id']) && $data['id'] == $this->model->id;
        //only check uniqueness of name if it was changed
        $nameUnchanged = isset($data['name']) && $data['name'] == $this->model->name;

        return [
            'id' => 'integer|min:1'.($idUnchanged ? '' : '|unique:association,id'),
            'name' => 'Required|between:4,80'.($nameUnchanged ? '' : '|unique:association,name'),
            'email' => 'email',
            'wuser_username' => 'Exists:wuser,username',
        ];
    }
}
