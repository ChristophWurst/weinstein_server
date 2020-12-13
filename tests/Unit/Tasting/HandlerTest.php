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

namespace Test\Unit\Tasting;

use App\Database\Repositories\CommissionRepository;
use App\Database\Repositories\CompetitionRepository;
use App\Database\Repositories\TasterRepository;
use App\Database\Repositories\TastingNumberRepository;
use App\Database\Repositories\TastingRepository;
use App\Database\Repositories\TastingSessionRepository;
use App\Database\Repositories\WineRepository;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\User;
use App\Tasting\Commission;
use App\Tasting\Handler;
use App\Tasting\TastingNumber;
use App\Tasting\TastingNumberValidator;
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;
use App\Validation\TastingNumberValidatorFactory;
use App\Wine;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Mockery\MockInterface;
use Test\TestCase;

class HandlerTest extends TestCase
{
    /** @var CommissionRepository|MockInterface */
    private $commissionRepository;

    /** @var CompetitionRepository|MockInterface */
    private $competitionRepository;

    /** @var TasterRepository|MockInterface */
    private $tasterRepository;

    /** @var TastingRepository|MockInterface */
    private $tastingRepository;

    /** @var TastingNumberRepository|MockInterface */
    private $tastingNumberRepository;

    /** @var TastingSessionRepository|MockInterface */
    private $tastingSessionRepository;

    /** @var WineRepository|MockInterface */
    private $wineRepository;

    /** @var TastingNumberValidatorFactory|MockInterface */
    private $tastingNumberValidatorFactory;

    /** @var Handler */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commissionRepository = Mockery::mock(CommissionRepository::class);
        $this->competitionRepository = Mockery::mock(CompetitionRepository::class);
        $this->tasterRepository = Mockery::mock(TasterRepository::class);
        $this->tastingRepository = Mockery::mock(TastingRepository::class);
        $this->tastingNumberRepository = Mockery::mock(TastingNumberRepository::class);
        $this->tastingSessionRepository = Mockery::mock(TastingSessionRepository::class);
        $this->wineRepository = Mockery::mock(WineRepository::class);
        $this->tastingNumberValidatorFactory = Mockery::mock(TastingNumberValidatorFactory::class);

        $this->handler = new Handler($this->commissionRepository, $this->competitionRepository, $this->tasterRepository,
            $this->tastingRepository, $this->tastingNumberRepository, $this->tastingSessionRepository, $this->wineRepository,
            $this->tastingNumberValidatorFactory);
    }

    public function testLockTastingNumbersInvalidTastingStage()
    {
        $this->expectException(Exception::class, 'invalid competition state');

        $competition = \Mockery::mock(Competition::class);
        $competitionState = \Mockery::mock(CompetitionState::class);
        $competition->shouldReceive('getAttribute')
            ->once()
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->once()
            ->with('description')
            ->andReturn('ENROLLMENT');

        $this->handler->lockTastingNumbers($competition);
    }

    public function testLockTastingNumbers()
    {
        $competition = new Competition();
        $competition->competition_state_id = 2;
        $competitionState = new CompetitionState();
        $competitionState->description = 'TASTINGNUMBERS1';
        $competition->competitionstate()->associate($competitionState);

        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->handler->lockTastingNumbers($competition);
    }

    public function testLockTasting1()
    {
        $competition = new Competition();
        $competition->competition_state_id = 2;
        $competitionState = new CompetitionState();
        $competitionState->description = 'TASTING1';
        $competition->competitionstate()->associate($competitionState);

        $tastingSession = new TastingSession();
        $tastingSession->locked = false;
        $competition->tastingsessions->add($tastingSession);

        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->tastingSessionRepository->shouldReceive('update')
            ->with($tastingSession, [
                'locked' => true,
        ]);

        $this->handler->lockTasting($competition);
    }

    public function testLockTasting2()
    {
        $competition = new Competition();
        $competition->competition_state_id = 2;
        $competitionState = new CompetitionState();
        $competitionState->description = 'TASTING2';
        $competition->competitionstate()->associate($competitionState);

        $tastingSession = new TastingSession();
        $tastingSession->locked = false;
        $competition->tastingsessions->add($tastingSession);

        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->tastingSessionRepository->shouldReceive('update')
            ->with($tastingSession, [
                'locked' => true,
        ]);

        $this->handler->lockTasting($competition);
    }

    public function testLockTastingInvalidTastingStage()
    {
        $competition = new Competition();
        $competition->competition_state_id = 2;
        $competitionState = new CompetitionState();
        $competitionState->description = 'ENROLLMENT';
        $competition->competitionstate()->associate($competitionState);

        $this->expectException(Exception::class);
        $this->handler->lockTasting($competition);
    }

    public function testLockKdb()
    {
        $competition = new Competition();
        $competition->competition_state_id = CompetitionState::STATE_KDB;
        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->handler->lockKdb($competition);
    }

    public function testLockExcluded()
    {
        $competition = new Competition();
        $competition->competition_state_id = CompetitionState::STATE_EXCLUDE;
        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->handler->lockKdb($competition);
    }

    public function testLockSosi()
    {
        $competition = new Competition();
        $competition->competition_state_id = CompetitionState::STATE_SOSI;
        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->handler->lockKdb($competition);
    }

    public function testLockChoosing()
    {
        $competition = new Competition();
        $competition->competition_state_id = CompetitionState::STATE_CHOOSE;
        $this->competitionRepository->shouldReceive('update')
            ->with($competition);

        $this->handler->lockKdb($competition);
    }

    public function testIsTastingFinished()
    {
        $competition = Mockery::mock(Competition::class);
        $tastingStage = new TastingStage();
        $competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn(new Collection());

        $this->assertTrue($this->handler->isTastingFinished($competition));
    }

    public function testIsTastingFinishedWithUntasted()
    {
        $competition = Mockery::mock(Competition::class);
        $tastingStage = new TastingStage();
        $competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn(new Collection([
                new TastingNumber(),
        ]));

        $this->assertFalse($this->handler->isTastingFinished($competition));
    }

    public function testCreateTastingNumber()
    {
        $competition = Mockery::mock(Competition::class);
        $data = [
            'wine_nr' => '123',
            'nr' => '3',
        ];
        $wine = Mockery::mock(Wine::class);
        $tastingStage = Mockery::mock(TastingStage::class);
        $tastingNumber = Mockery::mock(TastingNumber::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $validator = Mockery::mock(TastingNumberValidator::class);
        $this->tastingNumberValidatorFactory->shouldReceive('newValidator')
            ->with($competition, $data)
            ->andReturn($validator);
        $validator->shouldReceive('validateCreate')
            ->once();

        $this->wineRepository->shouldReceive('findByNr')
            ->with($competition, '123')
            ->andReturn($wine);
        $competition->shouldReceive('getTastingStage')
            ->andReturn($tastingStage);
        $this->tastingNumberRepository->shouldReceive('create')
            ->with($data, $wine, $tastingStage)
            ->andReturn($tastingNumber);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_TASTINGNUMBERS1);

        $result = $this->handler->createTastingNumber($data, $competition);

        $this->assertEquals($tastingNumber, $result);
    }

    public function testCreateTastingNumberAndBumpCompetitionState()
    {
        $competition = Mockery::mock(Competition::class);
        $data = [
            'wine_nr' => '123',
            'nr' => '3',
        ];
        $wine = Mockery::mock(Wine::class);
        $tastingStage = Mockery::mock(TastingStage::class);
        $tastingNumber = Mockery::mock(TastingNumber::class);
        $competitionState = Mockery::mock(CompetitionState::class);
        $validator = Mockery::mock(TastingNumberValidator::class);
        $this->tastingNumberValidatorFactory->shouldReceive('newValidator')
            ->with($competition, $data)
            ->andReturn($validator);
        $validator->shouldReceive('validateCreate')
            ->once();

        $this->wineRepository->shouldReceive('findByNr')
            ->with($competition, '123')
            ->andReturn($wine);
        $competition->shouldReceive('getTastingStage')
            ->andReturn($tastingStage);
        $this->tastingNumberRepository->shouldReceive('create')
            ->with($data, $wine, $tastingStage)
            ->andReturn($tastingNumber);
        $competition->shouldReceive('getAttribute')
            ->with('competitionState')
            ->andReturn($competitionState);
        $competitionState->shouldReceive('getAttribute')
            ->with('id')
            ->andReturn(CompetitionState::STATE_ENROLLMENT);
        $competition->shouldReceive('setAttribute')
            ->with('competition_state_id', CompetitionState::STATE_TASTINGNUMBERS1)
            ->once();
        $this->competitionRepository->shouldReceive('update')
            ->once()
            ->with($competition);

        $result = $this->handler->createTastingNumber($data, $competition);

        $this->assertEquals($tastingNumber, $result);
    }

    public function testImportTastingNumbers()
    {
    }

    public function testResetTastingNumbers()
    {
        $competition = Mockery::mock(Competition::class);
        $tastingStage = Mockery::mock(TastingStage::class);
        $competition->shouldReceive('getTastingStage')
            ->andReturn($tastingStage);
        $this->tastingNumberRepository->shouldReceive('deleteAll')
            ->with($competition, $tastingStage)
            ->once();

        $this->handler->resetTastingNumbers($competition);
    }

    public function testResetTastingNumbersWithInvalidState()
    {
        $competition = Mockery::mock(Competition::class);
        $competition->shouldReceive('getTastingStage')
            ->andReturn(null);
        $this->tastingNumberRepository->shouldReceive('deleteAll')
            ->never();
        $this->expectException(Exception::class);

        $this->handler->resetTastingNumbers($competition);
    }

    public function testDeleteTastingNumber()
    {
        $tastingNumber = new TastingNumber();

        $this->tastingNumberRepository->shouldReceive('delete')
            ->with($tastingNumber);

        $this->handler->deleteTastingNumber($tastingNumber);
    }

    public function testGetUntastedTastingNumbers()
    {
        $competition = new Competition();
        $tastingStage = new TastingStage();
        $tastingNumber = new TastingNumber();

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn([$tastingNumber]);

        $this->assertEquals([$tastingNumber], $this->handler->getUntastedTastingNumbers($competition, $tastingStage));
    }

    public function testGetAllTastingNumbers()
    {
        $competition = new Competition();
        $tastingStage = new TastingStage();
        $tastingNumber = new TastingNumber();

        $this->tastingNumberRepository->shouldReceive('findAllForCompetitionTastingStage')
            ->with($competition, $tastingStage)
            ->andReturn([$tastingNumber]);

        $this->assertEquals([$tastingNumber], $this->handler->getAllTastingNumbers($competition, $tastingStage));
    }

    public function testGetAllTastingSessionsAsAdmin()
    {
        $competition = new Competition();
        $tastingStage = new TastingStage();
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->once()->andReturn(true);

        $this->tastingSessionRepository->shouldReceive('findAll')
            ->with($competition, $tastingStage)
            ->andReturn([]);

        $this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, $user));
    }

    public function testGetAllTastingSessionsNoAdmin()
    {
        $competition = new Competition();
        $tastingStage = new TastingStage();

        $this->tastingSessionRepository->shouldReceive('findAll')
            ->with($competition, $tastingStage)
            ->andReturn([]);

        $this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, null));
    }

    public function testGetAllTastingSessionsAsNonAdmin()
    {
        $competition = new Competition();
        $tastingStage = new TastingStage();
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isAdmin')->once()->andReturn(false);

        $this->tastingSessionRepository->shouldReceive('findForUser')
            ->with($competition, $tastingStage, $user)
            ->andReturn([]);

        $this->assertEquals([], $this->handler->getAllTastingSessions($competition, $tastingStage, $user));
    }

    public function testCreateTastingSession()
    {
        // TODO: bypass validator
    }

    public function testUpdateTastingSession()
    {
        // TODO: bypass validator
    }

    public function testLockTastingSession()
    {
        $tastingSession = new TastingSession();

        $this->tastingSessionRepository->shouldReceive('update')
            ->with($tastingSession, [
                'locked' => true,
        ]);

        $this->handler->lockTastingSession($tastingSession);
    }

    public function testDeleteTastingSession()
    {
        $tastingSession = new TastingSession();

        $this->tastingSessionRepository->shouldReceive('delete')
            ->with($tastingSession);

        $this->handler->deleteTastingSession($tastingSession);
    }

    public function testAddTasterToTastingSession()
    {
        // TODO: bypass validator
    }

    public function testGetTastingSessionTasters()
    {
        $commission = new Commission();

        $this->tasterRepository->shouldReceive('findForCommission')
            ->once()
            ->with($commission);

        $this->handler->getCommissionTasters($commission);
    }

    public function testCreateTasting()
    {
        // TODO: bypass validator
    }

    public function testUpdateTasting()
    {
        // TODO: bypass validator
    }

    public function testGetNextTastingNumbersAllTasted()
    {
        $tastingSession = new TastingSession();
        $competition = Mockery::mock(Competition::class);
        $tastingSession->competition = $competition;
        $tastingStage = new TastingStage();
        $competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn(new Collection());

        $this->assertEquals([], $this->handler->getNextTastingNumbers($tastingSession));
    }

    public function testGetNextTastingNumbersOneLeft()
    {
        $tastingSession = new TastingSession();
        $competition = Mockery::mock(Competition::class);
        $tastingSession->competition = $competition;
        $tastingStage = new TastingStage();
        $competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);
        $tastingNumber1 = new TastingNumber();

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn(new Collection([
                $tastingNumber1,
        ]));

        $expected = [
            'a' => $tastingNumber1,
        ];
        $this->assertEquals($expected, $this->handler->getNextTastingNumbers($tastingSession));
    }

    public function testGetNextTastingNumbersTwoLeft()
    {
        $tastingSession = new TastingSession();
        $competition = Mockery::mock(Competition::class);
        $tastingSession->competition = $competition;
        $tastingStage = new TastingStage();
        $competition->shouldReceive('getTastingStage')->once()->andReturn($tastingStage);
        $tastingNumber1 = new TastingNumber();
        $tastingNumber2 = new TastingNumber();
        // fake two commissions
        $tastingSession->commissions->add(new Commission(['id' => 1]));
        $tastingSession->commissions->add(new Commission(['id' => 2]));
        $x = $tastingSession->commissions->count();

        $this->tastingNumberRepository->shouldReceive('findUntasted')
            ->with($competition, $tastingStage, 2)
            ->andReturn(new Collection([
                $tastingNumber1,
                $tastingNumber2,
        ]));

        $expected = [
            'a' => $tastingNumber1,
            'b' => $tastingNumber2,
        ];
        $this->assertEquals($expected, $this->handler->getNextTastingNumbers($tastingSession));
    }

    public function testIsTastingNumberTasted()
    {
        $tastingNumber = new TastingNumber();

        $this->tastingNumberRepository->shouldReceive('isTasted')
            ->with($tastingNumber)
            ->andReturn(false);

        $this->assertFalse($this->handler->isTastingNumberTasted($tastingNumber));
    }
}
