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
use Illuminate\Support\Collection;
use Weinstein\Applicant\ApplicantAccessController;
use Weinstein\Applicant\ApplicantDataProvider;
use Weinstein\Applicant\ApplicantHandler;

class ApplicantHandlerTest extends TestCase {

	public function tearDown() {
		Mockery::close();
	}

	public function testGetUsersApplicantsNoAdmin() {
		$user = new App\User();
		$user->admin = false;

		$dataProvider = Mockery::mock('Weinstein\Applicant\ApplicantDataProvider');
		$data = new Collection(array('test'));
		$dataProvider->shouldReceive('getApplicantsForUser')
			->with($user)
			->once()
			->andReturn($data);

		$service = new ApplicantHandler($dataProvider, new ApplicantAccessController);

		$this->assertSame($data, $service->getUsersApplicants($user));
	}

	public function testGetUsersApplicantsAsAdmin() {
		$user = new App\User();
		$user->admin = true;

		$dataProvider = Mockery::mock('Weinstein\Applicant\ApplicantDataProvider');
		$data = new Collection(array('test'));
		$dataProvider->shouldReceive('getAllApplicants')
			->once()
			->andReturn($data);

		$service = new ApplicantHandler($dataProvider, new ApplicantAccessController);

		$this->assertSame($data, $service->getUsersApplicants($user));
	}

	public function testIsAdmin() {
		$user = new App\User;
		$applicant = new Applicant();

		$accessController = Mockery::mock('Weinstein\Applicant\ApplicantAccessController');
		$accessController->shouldReceive('isAdmin')
			->with($user, $applicant)
			->andReturn(false);

		$service = new ApplicantHandler(new ApplicantDataProvider(), $accessController);

		$this->assertFalse($service->isAdmin($user, $applicant));
	}

}
