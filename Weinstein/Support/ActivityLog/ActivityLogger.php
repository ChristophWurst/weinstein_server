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

namespace Weinstein\Support\ActivityLog;

use InvalidArgumentException;
use Auth;
use App\Support\ActivityLog;

class ActivityLogger {

    /**
     * Log user activity
     * 
     * @param string $msg
     */
    public function log($msg) {
        if (is_null($msg) || !is_string($msg)) {
            throw new InvalidArgumentException();
        }

        $entry = new ActivityLog(array(
            'message' => $msg,
        ));
        //associate user
        $entry->user()->associate(Auth::user());
        Auth::user()->activitylogs()->save($entry);
    }

}
