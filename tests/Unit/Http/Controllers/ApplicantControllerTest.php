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
 */

namespace Test\Unit\Http\Controllers;

use App\Contracts\MasterDataStore;
use App\Exceptions\ValidationException;
use App\Http\Controllers\ApplicantController;
use App\MasterData\Applicant;
use App\MasterData\Association;
use App\MasterData\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\View\Factory as View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Laravel\BrowserKitTesting\TestResponse;
use Mockery;
use Mockery\MockInterface;
use Test\BrowserKitTestCase;

class ApplicantControllerTest extends BrowserKitTestCase
{
    use AuthorizationHelper;

    /** @var MasterDataStore|MockInterface */
    private $masterDataStore;

    /** @var AuthManager|MockInterface */
    private $auth;

    /** @var View|MockInterface */
    private $view;

    /** @var ApplicantController */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->masterDataStore = Mockery::mock(MasterDataStore::class);
        $this->auth = Mockery::mock(AuthManager::class);
        $this->view = Mockery::mock(View::class);

        $this->controller = Mockery::mock(ApplicantController::class,
                [
                $this->masterDataStore,
                $this->auth,
                $this->view,
            ])->makePartial();
        $this->controller->shouldReceive('authorize');
    }

    public function testIndex()
    {
        $user = Mockery::mock(User::class);
        $applicants = new Collection([
            Mockery::mock(Applicant::class),
        ]);

        $this->auth->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $this->masterDataStore->shouldReceive('getApplicants')
            ->once()
            ->with($user)
            ->andReturn($applicants);
        $this->view->shouldReceive('make')
            ->once()
            ->with('settings/applicant/index', [
                'applicants' => $applicants,
                'canAdd' => true,
            ])
            ->andReturn('view');
        $associations = Mockery::mock(Relation::class);
        $user->shouldReceive('associations')
            ->andReturn($associations);
        $associations->shouldReceive('exists')
            ->andReturn(true);

        $this->assertEquals('view', $this->controller->index());
    }

    public function testCreate()
    {
        $associations = new Collection();
        $user = Mockery::mock(User::class);
        $users = new Collection();

        $this->auth->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $this->masterDataStore->shouldReceive('getAssociations')
            ->with($user)
            ->once()
            ->andReturn($associations);
        $this->masterDataStore->shouldReceive('getUsers')
            ->once()
            ->andReturn($users);
        $this->view->shouldReceive('make')
            ->once()
            ->with('settings/applicant/form', [
                'create' => true,
                'associations' => [],
                'users' => ['none' => 'kein'],
            ])
            ->andReturn('view');

        $this->assertEquals('view', $this->controller->create());
    }

    public function testStoreValidationException()
    {
        $request = Mockery::mock(Request::class);
        $data = [
            'label' => 'Winzerhof',
            'wuser_username' => 'john',
        ];

        $request->shouldReceive('all')
            ->once()
            ->andReturn($data);
        $this->masterDataStore->shouldReceive('createApplicant')
            ->once()
            ->with($data)
            ->andThrow(new ValidationException());

        $this->response = TestResponse::fromBaseResponse($this->controller->store($request));

        $this->assertRedirectedToRoute('settings.applicants/create');
    }

    public function testStore()
    {
        $request = Mockery::mock(Request::class);
        $data = [
            'label' => 'Winzerhof',
            'wuser_username' => 'john',
        ];

        $applicant = Mockery::mock(Applicant::class);
        $user = Mockery::mock(User::class);
        $password = 'hello';
        $request->shouldReceive('all')
            ->once()
            ->andReturn($data);
        $this->masterDataStore->shouldReceive('createApplicant')
            ->once()
            ->with($data)
            ->andReturn([$applicant, $user, $password]);
        $session = Mockery::mock(\Illuminate\Contracts\Session\Session::class);
        $user->shouldReceive('getAttribute')
            ->with('username')
            ->andReturn('user');
        $request->shouldReceive('session')
            ->once()
            ->andReturn($session);
        $session->shouldReceive('flash')
            ->once()
            ->with('applicant_created', ['user', 'hello']);

        $this->response = TestResponse::fromBaseResponse($this->controller->store($request));

        $this->assertRedirectedToRoute('settings.applicants');
    }

    public function testShow()
    {
        $applicant = Mockery::mock(Applicant::class);

        $this->view->shouldReceive('make')
            ->once()
            ->with('settings/applicant/show', [
                'data' => $applicant,
            ])
            ->andReturn('view');

        $this->assertEquals('view', $this->controller->show($applicant));
    }

    public function testGetImport()
    {
        $this->view->shouldReceive('make')
            ->with('settings/applicant/import')
            ->andReturn('view');

        $this->assertEquals('view', $this->controller->getImport());
    }

    public function testPostImportNoFile()
    {
        $request = Mockery::mock(Request::class);

        $request->shouldReceive('hasFile')
            ->once()
            ->with('xlsfile')
            ->andReturn(false);
        $this->masterDataStore->shouldNotReceive('importApplicants');

        $this->controller->postImport($request);
    }

    public function testPostImport()
    {
        $request = Mockery::mock(Request::class);
        $file = new UploadedFile(__FILE__, 'some file');

        $request->shouldReceive('hasFile')
            ->once()
            ->with('xlsfile')
            ->andReturn(true);
        $request->shouldReceive('file')
            ->once()
            ->andReturn($file);
        $this->masterDataStore->shouldReceive('importApplicants')
            ->once()
            ->with($file)
            ->andReturn(13);
        /* Session::shouldReceive('flash')
          ->once()
          ->with('rowsImported', 13); */

        $this->response = TestResponse::fromBaseResponse($this->controller->postImport($request));
        $this->assertRedirectedToRoute('settings.applicants');
    }

    public function testEdit()
    {
        $applicant = Mockery::mock(Applicant::class);
        $association = Mockery::mock(Association::class);
        $user = Mockery::mock(User::class);
        $associations = new Collection();
        $users = new Collection();

        $this->auth->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $applicant->shouldReceive('getAttribute')
            ->once()
            ->with('association')
            ->andReturn($association);
        $association->shouldReceive('administrates')
            ->once()
            ->with($user)
            ->andReturn(true);
        $this->masterDataStore->shouldReceive('getAssociations')
            ->once()
            ->andReturn($associations);
        $this->masterDataStore->shouldReceive('getUsers')
            ->once()
            ->andReturn($users);
        $this->view->shouldReceive('make')
            ->once()
            ->with('settings/applicant/form',
                [
                'create' => false,
                'applicant' => $applicant,
                'associations' => [],
                'editId' => true,
                'users' => ['none' => 'kein'],
            ])
            ->andReturn('view');

        $this->assertEquals('view', $this->controller->edit($applicant));
    }

    public function testUpdate()
    {
        // TODO
    }
}
