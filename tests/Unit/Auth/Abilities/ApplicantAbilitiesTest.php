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

namespace Test\Unit\Auth\Abilities;

use App\Auth\Abilities\ApplicantAbilities;
use App\MasterData\Applicant;
use App\MasterData\Association;
use Illuminate\Database\Eloquent\Relations\Relation;
use Mockery;
use Test\TestCase;

class ApplicantAbilitiesTest extends TestCase {

	use AbilitiesMock;

	/** @var ApplicantAbilities */
	private $abilities;

	protected function setUp() {
		parent::setUp();

		$this->abilities = new ApplicantAbilities();
	}

	public function testShowEditApplicantAdmin() {
		$user = $this->getUserMock();
		$applicant = Mockery::mock(Applicant::class);

		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('hans');
		$applicant->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('hans');

		$this->assertTrue($this->abilities->show($user, $applicant));
		$this->assertTrue($this->abilities->edit($user, $applicant));
	}

	public function testShowEditAssociationAdmin() {
		$user = $this->getUserMock();
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);

		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('hans');
		$applicant->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('ferdinand');
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$association->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('hans');

		$this->assertTrue($this->abilities->show($user, $applicant));
		$this->assertTrue($this->abilities->edit($user, $applicant));
	}

	public function testShowEditNoAccess() {
		$user = $this->getUserMock();
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);

		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('hans');
		$applicant->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('ferdinand');
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$association->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('maria');

		$this->assertFalse($this->abilities->show($user, $applicant));
		$this->assertFalse($this->abilities->edit($user, $applicant));
	}

	public function testCreateAsAssocAdmin() {
		$user = $this->getUserMock();
		$associations = Mockery::mock(Relation::class);
		$user->shouldReceive('associations')
			->once()
			->andReturn($associations);
		$associations->shouldReceive('exists')
			->andReturn(true);

		$this->assertTrue($this->abilities->create($user));
	}

	public function testCreateAsSimpleUser() {
		$user = $this->getUserMock();
		$associations = Mockery::mock(Relation::class);
		$user->shouldReceive('associations')
			->once()
			->andReturn($associations);
		$associations->shouldReceive('exists')
			->andReturn(false);

		$this->assertFalse($this->abilities->create($user));
	}

	public function testImport() {
		$this->assertFalse($this->abilities->import($this->getUserMock()));
	}

	public function testEditAsAssocAdmin() {
		$user = $this->getUserMock();
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('gerda');
		$applicant->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn(null);
		$association->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('gerda');

		$this->assertTrue($this->abilities->edit($user, $applicant));
	}

	public function testEditAsSimpleUser() {
		$user = $this->getUserMock();
		$applicant = Mockery::mock(Applicant::class);
		$association = Mockery::mock(Association::class);
		$applicant->shouldReceive('getAttribute')
			->with('association')
			->andReturn($association);
		$user->shouldReceive('getAttribute')
			->with('username')
			->andReturn('gerda');
		$applicant->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn(null);
		$association->shouldReceive('getAttribute')
			->with('wuser_username')
			->andReturn('alfred');

		$this->assertFalse($this->abilities->edit($user, $applicant));
	}

}
