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

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * @property int $id
 * @property int $taster_id
 * @property Taster $taster
 * @property int $tastingnumber_id
 * @property TastingNumber $tastingnumber
 * @property int $rating
 */
class Tasting extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'tasting';

    /**
     * Attributes for mass assignment.
     *
     * @var array
     */
    protected $fillable = [
        'tastingnumber_id',
        'taster_id',
        'rating',
    ];

    /**
     * 1 tasting : 1 taster.
     *
     * @return Relation
     */
    public function taster()
    {
        return $this->belongsTo(Taster::class);
    }

    /**
     * 1 tasting : 1 tasting number.
     *
     * @return Relation
     */
    public function tastingnumber()
    {
        return $this->belongsTo(TastingNumber::class);
    }
}
