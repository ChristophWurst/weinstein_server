<?php

use App\Database\Repositories\AssociationRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\UserRepository;
use App\MasterData\Association;
use App\MasterData\Store;
use App\MasterData\User;
use Illuminate\Support\Collection;

class StoreTest extends TestCase {

	/** @var AssociationRepository|PHPUnit_Framework_MockObject_MockObject */
	private $associationRepository;

	/** @var CompetitionRepository|PHPUnit_Framework_MockObject_MockObject */
	private $competitionRepository;

	/** @var UserRepository|PHPUnit_Framework_MockObject_MockObject */
	private $userRepository;

	/** @var Store */
	private $store;

	protected function setUp() {
		parent::setUp();

		$this->associationRepository = $this->getSimpleClassMock(AssociationRepository::class);
		$this->competitionRepository = $this->getSimpleClassMock(CompetitionRepository::class);
		$this->userRepository = $this->getSimpleClassMock(UserRepository::class);
		$this->store = new Store($this->associationRepository, $this->userRepository);
	}

	public function testGetAllAssociations() {
		$collection = new Collection();

		$this->associationRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getAssociations());
	}

	public function testGetAllAssociationsAsAdmin() {
		$collection = new Collection();
		$user = new User([
			'admin' => true,
		]);

		$this->associationRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getAssociations());
	}

	public function testGetAssociationsAsNonAdmin() {
		$collection = new Collection();
		$user = new User();

		$this->associationRepository->expects($this->once())
			->method('findForUser')
			->with($user)
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getAssociations($user));
	}

	public function testCreateAssociation() {
		$data = [
			'id' => 40,
			'name' => 'Pulkau',
		];

		$association = new Association($data);
		$this->associationRepository->expects($this->once())
			->method('create')
			->with($data)
			->will($this->returnValue($association));

		$this->assertEquals($association, $this->store->createAssociation($data));
	}

	/**
	 * @expectedException Weinstein\Exception\ValidationException
	 */
	public function testCreateAssociationValidationFails() {
		$data = [
			'id' => -1,
			'name' => 'Pulkau',
		];

		$association = new Association($data);
		$this->associationRepository->expects($this->never())
			->method('create');

		$this->store->createAssociation($data);
	}

	public function testUpdateAssociation() {
		$association = new Association();
		$data = [
			'id' => 33,
			'name' => 'Pillersdorf',
		];

		$this->associationRepository->expects($this->once())
			->method('update')
			->with($association, $data);

		$this->store->updateAssociation($association, $data);
	}

	/**
	 * @expectedException Weinstein\Exception\ValidationException
	 */
	public function testUpdateAssociationValidationFails() {
		$association = new Association();
		$data = [
			'id' => -5,
			'name' => 'Pillersdorf',
		];

		$this->associationRepository->expects($this->never())
			->method('update');

		$this->store->updateAssociation($association, $data);
	}

	public function testGetAllCompetitions() {
		$collection = new Collection();

		$this->competitionRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getCompetitions());
	}

	public function testGetAllUsers() {
		$collection = new Collection();

		$this->userRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getUsers());
	}

	public function testGetUsersAsAdmin() {
		$collection = new Collection();
		$user = new User();
		$user->admin = true;

		$this->userRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getUsers($user));
	}

	public function testGetUsersAsNonAdmin() {
		$user = new User();
		$user->admin = false;
		$collection = new Collection([$user]);

		$this->userRepository->expects($this->never())
			->method('findAll');

		$this->assertEquals($collection, $this->store->getUsers($user));
	}

	public function testCreateUser() {
		$data = [
			'username' => 'jane123',
			'password' => '123456',
		];

		$expected = new User($data);
		$this->userRepository->expects($this->once())
			->method('create')
			->with($data)
			->will($this->returnValue($expected));

		$actual = $this->store->createUser($data);

		$this->assertEquals($expected, $actual);
	}

	public function testUpdateUser() {
		$user = new User([
			'user123',
			'password456',
		]);
		$data = [
			'username' => 'jane123',
			'password' => '123456',
		];

		$this->userRepository->expects($this->once())
			->method('update')
			->with($user, $data);

		$this->store->updateUser($user, $data);
	}

	public function testDeleteUser() {
		$user = new User([
			'user123',
			'password456',
		]);

		$this->userRepository->expects($this->once())
			->method('delete')
			->with($user);

		$this->store->deleteUser($user);
	}

}
