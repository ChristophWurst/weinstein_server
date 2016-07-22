<?php

namespace App\Http\Middleware;

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

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Contracts\Auth\Guard;

class RedirectUnauthenticated {

	/**
	 *
	 * @var \Illuminate\Contracts\Auth
	 */
	private $auth;

	/**
	 *
	 * @var \Illuminate\Contracts\Logging\Log
	 */
	private $log;

	/**
	 * 
	 * @param Guard $auth
	 * @param Log $log
	 */
	public function __construct(Guard $auth, Log $log) {
		$this->auth = $auth;
		$this->log = $log;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next) {
		if (!$this->auth->check() && !$request->is('/') && !$request->is('login')) {
			$this->log->warning('redirecting unauthorized request to ' . $request->fullUrl());
			if ($request->ajax()) {
				return response('Unauthorized', 401);
			} else {
				return redirect()->route('login');
			}
		}

		return $next($request);
	}

}
