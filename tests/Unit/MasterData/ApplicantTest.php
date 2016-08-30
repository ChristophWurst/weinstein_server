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

namespace Test\Unit\MasterData;

use App\MasterData\Applicant;
use Test\TestCase;

class ApplicantTest extends TestCase {

	use \Way\Tests\ModelHelpers;

	public function testBelongsToAddress() {
		$this->assertBelongsTo('address', Applicant::class);
	}

	public function testBelongsToAssociation() {
		$this->assertBelongsTo('association', Applicant::class);
	}

	public function testBelognsToUser() {
		$this->assertBelongsTo('user', Applicant::class);
	}

	public function testHasManyWines() {
		$this->assertHasMany('wines', Applicant::class);
	}

}
