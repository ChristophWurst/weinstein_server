<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\BaseTestCase;

abstract class TestCase extends BaseTestCase {

	/**
	 * The base URL to use while testing the application.
	 *
	 * @var string
	 */
	protected $baseUrl = 'http://localhost';

	/**
	 * Creates the application.
	 *
	 * @return Application
	 */
	public function createApplication() {
		$app = require __DIR__ . '/../bootstrap/app.php';

		$app->make(Kernel::class)->bootstrap();

		return $app;
	}

	public function getSimpleClassMock($class) {
		return $this->getMockBuilder($class)
				->disableOriginalConstructor()
				->getMock();
	}

}
