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

namespace App\Http\Controllers;

use App\Contracts\ActivityLogger;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ActivityLogController extends BaseController
{
    /** @var Factory */
    private $viewFactory;

    /** @var ActivityLogger */
    private $activityLogger;

    public function __construct(ActivityLogger $activityLogger, Factory $viewFactory)
    {
        $this->activityLogger = $activityLogger;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Show all logs.
     *
     * @return View
     */
    public function index(): View
    {
        $this->authorize('view-activitylog');

        return $this->viewFactory->make('settings/activitylog/index', [
            'logs' => $this->activityLogger->getMostRecentLogs(),
        ]);
    }
}
