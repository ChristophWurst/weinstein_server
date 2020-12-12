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

namespace App\Support\Activity;

use App\Contracts\ActivityLogger;
use App\Database\Repositories\ActivityLogRepository;
use App\MasterData\User;

class DatabaseLogger implements ActivityLogger
{
    /** @var ActivityLogRepository */
    private $activityLogRepository;

    public function __construct(ActivityLogRepository $activityLogRepository)
    {
        $this->activityLogRepository = $activityLogRepository;
    }

    public function log($message)
    {
        return $this->activityLogRepository->create($message);
    }

    public function logUserAction($message, User $user)
    {
        return $this->activityLogRepository->create($message, $user);
    }

    public function getMostRecentLogs($limit = 200)
    {
        return $this->activityLogRepository->findMostRecent($limit);
    }
}
