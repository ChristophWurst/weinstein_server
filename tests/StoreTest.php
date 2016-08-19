<?php

use App\Database\Repositories\UserRepository;
use App\MasterData\Store;
use App\MasterData\User;
use Illuminate\Support\Collection;

class StoreTest extends TestCase {

	/** @var UserRepository|PHPUnit_Framework_MockObject_MockObject */
	private $userRepository;

	/** @var Store */
	private $store;

	protected function setUp() {
		parent::setUp();

		$this->userRepository = $this->getSimpleClassMock(UserRepository::class);
		$this->store = new Store($this->userRepository);
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
