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

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Http\Controllers\BaseController;
use App\Competition\Competition;

class EvaluationController extends BaseController {

	/**
	 * Filter user administrates competition
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	public function filterAdministrates($route, $request) {
		$user = Auth::user();
		$competition = Route::input('competition');

		if (!$user->admin && $competition->user()->username !== $user->username) {
			$this->abortNoAccess($route, $request);
		}
	}

	/**
	 * 
	 */
	public function __construct() {
		parent::__construct();

		$this->beforeFilter('@filterAdministrates');
	}

	/**
	 * 
	 * @param Competition $competition
	 * @return Response
	 */
	public function protocols(Competition $competition) {
		return View::make('competition/evaluation/index')
				->withCompetition($competition)
				->withTastingSessions1($competition->tastingsessions()->whereTastingstage_id(1)->get())
				->withTastingSessions2($competition->tastingsessions()->whereTastingstage_id(2)->get());
	}

}
