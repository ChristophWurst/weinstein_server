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
use App\MasterData\User;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Test\TestCase;

class ActivityLogControllerTest extends TestCase {

	use AuthorizationHelper;

	/** @var ActivityLogger|\Mockery\MockInterface */
	private $activityLogger;

	protected function setUp() {
		parent::setUp();

		$this->activityLogger = Mockery::mock(ActivityLogger::class);
		$this->app->instance(ActivityLogger::class, $this->activityLogger);
	}

	public function testIndex() {
		$data = new Collection();

		$this->be($this->getAdminMock());
		$this->activityLogger->shouldReceive('getMostRecentLogs')
			->once()
			->andReturn($data);

		$this->get('settings/activitylog');

		$this->assertResponseOk();
		$this->assertViewHas('logs', $data);
	}

}
