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
 */
class TastingStage extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'tastingstage';

    /**
     * 1 tasting stage : n tasting numbers.
     *
     * @return Relation
     */
    public function tastingnumbers()
    {
        return $this->hasMany(TastingNumber::class);
    }

    /**
     * 1 tasting stage : n tasting sessions.
     *
     * @return Relation
     */
    public function tastingsessions()
    {
        return $this->hasMany(TastingSession::class);
    }
}
