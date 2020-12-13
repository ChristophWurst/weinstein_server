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

class WineSortValidator extends Validator
{
    protected $modelClass = WineSort::class;

    /**
     * Get attributes names.
     *
     * @return array
     */
    protected function getAttributeNames()
    {
        return [
            'order' => 'Sortennummer',
            'name' => 'Bezeichnung',
        ];
    }

    /**
     * Get rules for creating a new wine sort.
     *
     * @param array $data
     * @return array
     */
    protected function getCreateRules(array $data)
    {
        return [
            'order' => 'Required|Integer|Min:1'
            .'|Unique:winesort,order,',
            'name' => 'Required|Min:2|Max:30'
            .'|Unique:winesort,name,',
        ];
    }

    /**
     * Get rules for updating an existing wine sort.
     *
     * @param array $data
     * @param Model $model
     * @return array
     */
    protected function getUpdateRules(array $data, Model $model = null)
    {
        $orderUnchanged = isset($data['order']) && $data['order'] == $this->model->order;
        $nameUnchanged = isset($data['name']) && $data['name'] === $this->model->name;

        return [
            'order' => $orderUnchanged ? '' : ('Required|Integer|Min:1'
                .'|Unique:winesort,order,'),
            'name' => $nameUnchanged ? '' : ('Required|Min:2|Max:30'
                .'|Unique:winesort,name,'),
        ];
    }
}
