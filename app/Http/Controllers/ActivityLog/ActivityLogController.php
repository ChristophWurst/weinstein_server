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

namespace App\Http\Controllers\ActivityLog;

use Illuminate\Support\Facades\View;
use App\Http\Controllers\BaseController;
use App\Support\ActivityLog;

class ActivityLogController extends BaseController {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		//register filters
		$this->beforeFilter('@filterAdmin');
	}

	/**
	 * Show all logs
	 * 
	 * @return Response
	 */
	public function index() {
		return View::make('settings/activitylog/index')->with('logs', ActivityLog::orderBy('created_at', 'desc')->get());
	}

}
