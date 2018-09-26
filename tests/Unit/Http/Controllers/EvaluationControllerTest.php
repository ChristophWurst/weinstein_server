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

namespace Test\Unit\Http\Controllers;

use App\Http\Controllers\EvaluationController;
use Illuminate\Contracts\View\Factory;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class EvaluationControllerTest extends BrowserKitTestCase {

	/** @var Factory|MockInterface */
	private $view;

	/** @var EvaluationController|MockInterface */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->view = Mockery::mock(Factory::class);

		$this->controller = Mockery::mock(EvaluationController::class, [
				$this->view,
			])->makePartial();
	}

	public function testProtocols() {
		$this->markTestSkipped('not mockable yet');
	}

}
