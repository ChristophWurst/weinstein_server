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

use App\MasterData\Competition;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class BaseController extends Controller {

	use AuthorizesRequests;
	
	/** @var Competition */
	protected $competition;

	/** @var array */
	protected $selectNone = [
	    'none' => 'kein',
	];

	/**
	 * Abort app because user has no access
	 * 
	 * @param Route $route
	 * @param Request $request
	 */
	protected function abortNoAccess($route, $request) {
		App::abort(403);
	}

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout() {
		if (!is_null($this->layout)) {
			$this->layout = View::make($this->layout);
		}
	}

	/**
	 * Constructor
	 * 
	 * loads session variable
	 * 
	 */
	public function __construct() {
		$this->competition = Route::input('competition');
		if (!is_null($this->competition)) {
			View::share('competition', $this->competition);
		}
	}

}
