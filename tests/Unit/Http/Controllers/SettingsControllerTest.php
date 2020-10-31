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

use App\Http\Controllers\SettingsController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class SettingsControllerTest extends BrowserKitTestCase {

	/** @var Factory|MockInterface */
	private $view;
	
	/** @var SettingsController */
	private $controller;
	
	protected function setUp(): void {
		parent::setUp();

		$this->view = Mockery::mock(Factory::class);

		$this->controller = new SettingsController($this->view);
	}

	public function testIndex() {
		$view = Mockery::mock(View::class);

		$this->view->shouldReceive('make')
			->once()
			->with('settings/index')
			->andReturn($view);

		$this->assertSame($view, $this->controller->index());
	}

}
