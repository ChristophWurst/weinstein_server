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

namespace Test\Unit;

use App\Wine;
use Test\TestCase;

class WineTest extends TestCase {

	use \Way\Tests\ModelHelpers;

	public function testBelongsToApplicant() {
		$this->assertBelongsTo('applicant', Wine::class);
	}

	public function testBelongsToCompetition() {
		$this->assertBelongsTo('competition', Wine::class);
	}

	public function testBelongsToWineQuality() {
		$this->assertBelongsTo('winequality', Wine::class);
	}

	public function testBelongsToWineSort() {
		$this->assertBelongsTo('winesort', Wine::class);
	}

	public function testHasManyTastingNubers() {
		$this->assertHasMany('tastingnumbers', Wine::class);
	}

}
