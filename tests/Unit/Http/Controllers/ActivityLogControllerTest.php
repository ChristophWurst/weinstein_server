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

use App\Contracts\ActivityLogger;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class ActivityLogControllerTest extends BrowserKitTestCase {

	use AuthorizationHelper;

	/** @var ActivityLogger|MockInterface */
	private $activityLogger;

	/** @var Factory|MockInterface */
	private $viewFactory;

	/** @var ActivityLogController */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->activityLogger = Mockery::mock(ActivityLogger::class);
		$this->viewFactory = Mockery::mock(Factory::class);
		$this->controller = new ActivityLogController($this->activityLogger, $this->viewFactory);
	}

	public function testIndex() {
		$data = new Collection();

		$this->be($this->getAdminMock());
		$this->activityLogger->shouldReceive('getMostRecentLogs')
			->once()
			->andReturn($data);
		$this->viewFactory->shouldReceive('make')
			->once()
			->with('settings/activitylog/index', [
				'logs' => $data,
			])
			->andReturn('view');

		$this->assertEquals('view', $this->controller->index());
	}

}
