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

use App\Wine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property int $order
 * @property string $name
 */
class WineSort extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'winesort';

    /**
     * Mass assignment attributes.
     *
     * @var array of string
     */
    protected $fillable = [
        'order',
        'name',
        'competition_id',
    ];

    /**
     * The attributes that should be hidden for arrays/json.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    /**
     * @return string
     */
    public function getSelectLabelAttribute()
    {
        return $this->order.' - '.$this->name;
    }

    /**
     * 1 sort : n wines.
     *
     * @return Relation
     */
    public function wines()
    {
        return $this->hasMany(Wine::class, 'winesort_id');
    }
}
