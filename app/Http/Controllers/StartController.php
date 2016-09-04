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

use Illuminate\Contracts\View\Factory;
use Symfony\Component\HttpFoundation\Response;

class StartController extends BaseController {

	/** @var Factory */
	private $viewFactory;

	/**
	 * @param Factory $viewFactory
	 */
	public function __construct(Factory $viewFactory) {
		$this->viewFactory = $viewFactory;
	}

	/**
	 * Show start page
	 * 
	 * @return Response
	 */
	public function index() {
		return $this->viewFactory->make('index');
	}

}
