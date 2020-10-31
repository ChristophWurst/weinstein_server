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

namespace Test\Unit\Validation;

use App\Validation\WineValidatorFactory;
use App\Wine;
use App\Wine\WineValidator;
use Mockery;
use Test\TestCase;

class WineValidatorFactoryTest extends TestCase {

	/** @var WineValidatorFactory */
	private $factory;

	protected function setUp(): void {
		parent::setUp();

		$this->factory = new WineValidatorFactory();
	}

	public function testNewWineValidator() {
		$wine = Mockery::mock(Wine::class);
		$data = [
			'label' => 'Superwein',
			'sugar' => 12.3,
		];

		$validator = $this->factory->newWineValidator($wine, $data);

		$this->assertInstanceOf(WineValidator::class, $validator);
	}

}
