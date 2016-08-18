<?php

use App\Database\Repositories\UserRepository;
use App\MasterData\Store;
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

	public function testGetUsers() {
		$collection = new Collection();

		$this->userRepository->expects($this->once())
			->method('findAll')
			->will($this->returnValue($collection));

		$this->assertEquals($collection, $this->store->getUsers());
	}

}
