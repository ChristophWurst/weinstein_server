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

use App\Http\Controllers\BaseController;
use App\MasterData\Competition;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Response;

class EvaluationController extends BaseController {

	/** @var Factory */
	private $viewFactory;

	/**
	 * @param Factory $viewFactory
	 */
	public function __construct(Factory $viewFactory) {
		$this->viewFactory = $viewFactory;
	}

	/**
	 * @param Competition $competition
	 * @return Response
	 */
	public function protocols(Competition $competition) {
		$this->authorize('show-evaluations');

		return $this->viewFactory->make('competition/evaluation/index', [
			'competition' => $competition,
			'tastingSession1' => $competition->tastingsessions()->whereTastingstage_id(1)->get(),
			'tastingSession2' => $competition->tastingsessions()->whereTastingstage_id(2)->get(),
		]);
	}

}
