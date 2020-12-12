<?php

namespace Test\Unit\MasterData;

use App\Database\Repositories\ApplicantRepository;
use App\Database\Repositories\AssociationRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\UserRepository;
use App\Database\Repositories\WineSortRepository;
use App\Exceptions\ValidationException;
use App\MasterData\Association;
use App\MasterData\Store;
use App\MasterData\User;
use App\MasterData\WineSort;
use Illuminate\Support\Collection;
use PHPUnit_Framework_MockObject_MockObject;
use Test\TestCase;

class StoreTest extends TestCase
{
    /** @var ApplicantRepository|PHPUnit_Framework_MockObject_MockObject */
    private $applicantRepository;

    /** @var AssociationRepository|PHPUnit_Framework_MockObject_MockObject */
    private $associationRepository;

    /** @var CompetitionRepository|PHPUnit_Framework_MockObject_MockObject */
    private $competitionRepository;

    /** @var UserRepository|PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;

    /** @var WineSortRepository|PHPUnit_Framework_MockObject_MockObject */
    private $wineSortRepository;

    /** @var Store */
    private $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->applicantRepository = $this->getSimpleClassMock(ApplicantRepository::class);
        $this->associationRepository = $this->getSimpleClassMock(AssociationRepository::class);
        $this->competitionRepository = $this->getSimpleClassMock(CompetitionRepository::class);
        $this->userRepository = $this->getSimpleClassMock(UserRepository::class);
        $this->wineSortRepository = $this->getSimpleClassMock(WineSortRepository::class);
        $this->store = new Store($this->applicantRepository, $this->associationRepository, $this->competitionRepository,
            $this->userRepository, $this->wineSortRepository);
    }

    public function testGetAllAssociations()
    {
        $collection = new Collection();

        $this->associationRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getAssociations());
    }

    public function testGetAllAssociationsAsAdmin()
    {
        $collection = new Collection();

        $this->associationRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getAssociations());
    }

    public function testGetAssociationsAsNonAdmin()
    {
        $collection = new Collection();
        $user = new User();

        $this->associationRepository->expects($this->once())
            ->method('findForUser')
            ->with($user)
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getAssociations($user));
    }

    public function testCreateAssociation()
    {
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

    public function testCreateAssociationValidationFails()
    {
        $data = [
            'id' => -1,
            'name' => 'Pulkau',
        ];

        $association = new Association($data);
        $this->associationRepository->expects($this->never())
            ->method('create');

        $this->expectException(ValidationException::class);
        $this->store->createAssociation($data);
    }

    public function testUpdateAssociation()
    {
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

    public function testUpdateAssociationValidationFails()
    {
        $association = new Association();
        $data = [
            'id' => -5,
            'name' => 'Pillersdorf',
        ];

        $this->associationRepository->expects($this->never())
            ->method('update');

        $this->expectException(ValidationException::class);
        $this->store->updateAssociation($association, $data);
    }

    public function testGetAllCompetitions()
    {
        $collection = new Collection();

        $this->competitionRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getCompetitions());
    }

    public function testGetAllUsers()
    {
        $collection = new Collection();

        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getUsers());
    }

    public function testGetUsersAsAdmin()
    {
        $collection = new Collection();
        $user = new User();
        $user->admin = true;

        $this->userRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getUsers($user));
    }

    public function testGetUsersAsNonAdmin()
    {
        $user = new User();
        $user->admin = false;
        $collection = new Collection([$user]);

        $this->userRepository->expects($this->never())
            ->method('findAll');

        $this->assertEquals($collection, $this->store->getUsers($user));
    }

    public function testCreateUser()
    {
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

    public function testUpdateUser()
    {
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

    public function testDeleteUser()
    {
        $user = new User([
            'user123',
            'password456',
        ]);

        $this->userRepository->expects($this->once())
            ->method('delete')
            ->with($user);

        $this->store->deleteUser($user);
    }

    public function testGetWineSorts()
    {
        $collection = new Collection();

        $this->wineSortRepository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($collection));

        $this->assertEquals($collection, $this->store->getWineSorts());
    }

    public function testCreateWineSort()
    {
        $data = [
            'order' => 13,
            'name' => 'Grüner Veltliner',
        ];

        $result = new WineSort($data);
        $this->wineSortRepository->expects($this->once())
            ->method('create')
            ->with($data)
            ->will($this->returnValue($result));

        $this->assertEquals($result, $this->store->createWineSort($data));
    }

    public function testUpdateWineSort()
    {
        $old = new WineSort([
            'order' => 21,
            'name' => 'Blauburger',
        ]);

        $data = [
            'order' => 13,
            'name' => 'Grüner Veltliner',
        ];

        $this->wineSortRepository->expects($this->once())
            ->method('update')
            ->with($old, $data);

        $this->store->updateWineSort($old, $data);
    }
}
