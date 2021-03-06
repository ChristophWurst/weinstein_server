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

namespace App\Http\Controllers;

use App\Contracts\TastingHandler;
use App\Exceptions\IllegalTastingStageException;
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\MasterData\TastingProtocol;
use App\MasterData\User;
use App\Tasting\Commission;
use App\Tasting\TastingSession;
use App\Tasting\TastingStage;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as Response2;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TastingSessionController extends BaseController
{
    /** @var TastingHandler */
    private $tastingHandler;

    /** @var Factory */
    private $viewFactory;

    /**
     * @param TastingHandler $tastingHandler
     * @param Factory $viewFactory
     */
    public function __construct(TastingHandler $tastingHandler, Factory $viewFactory)
    {
        $this->tastingHandler = $tastingHandler;
        $this->viewFactory = $viewFactory;
    }

    /**
     * Share common view data.
     *
     * @param Competition $competition
     */
    private function shareCommonViewData(Competition $competition)
    {
        $tastingStage = $competition->getTastingStage();
        if (is_null($tastingStage)) {
            throw new InvalidCompetitionStateException();
        }

        $this->viewFactory->share('competition', $competition);
        $this->viewFactory->share('tastingstage', $tastingStage);
        $this->viewFactory->share('tastingsessions',
            $this->tastingHandler->getAllTastingSessions($competition, $tastingStage, Auth::user()));
        $tasting1 = $competition->competitionState->id === CompetitionState::STATE_TASTING1;
        $tasting2 = $competition->competitionState->id === CompetitionState::STATE_TASTING2;
        $this->viewFactory->share('show_finish1',
            $tasting1 && $competition->wine_details()->count() === $competition->wine_details()->whereNotNull('rating1')->count());
        $this->viewFactory->share('show_finish2',
            $tasting2 && $competition->wines()->withTastingNumber(TastingStage::find(2))->count() === $competition->wine_details()->withTastingNumber(TastingStage::find(2))->whereNotNull('rating2')->count());
    }

    private function checkCompetitionState(Competition $competition)
    {
        // TODO: might make sense to move to BL layer
        if ($competition->competitionState->id !== CompetitionState::STATE_TASTING1 && $competition->competitionState->id !== CompetitionState::STATE_TASTING2) {
            throw new IllegalTastingStageException();
        }
    }

    private function checkTastingSessionLocked(TastingSession $tastingSession)
    {
        if ($tastingSession->locked) {
            throw new IllegalTastingStageException();
        }
    }

    /**
     * list current competitions tasting sessions.
     *
     * @param Competition $competition
     * @return View
     */
    public function index(Competition $competition)
    {
        $this->authorize('show-tastingsessions', $competition);
        $this->checkCompetitionState($competition);

        $this->shareCommonViewData($competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/index');
    }

    /**
     * Show form for adding new session.
     *
     * @param Competition $competition
     * @return View
     */
    public function add(Competition $competition)
    {
        $this->authorize('create-tastingsession', $competition);
        $this->checkCompetitionState($competition);

        $this->shareCommonViewData($competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/form',
                [
                'users' => $this->selectNone + User::all()->pluck('username', 'username')->all(),
        ]);
    }

    /**
     * Validate and store newly created sessions.
     *
     * @param Competition $competition
     *
     * @return RedirectResponse
     */
    public function store(Competition $competition): RedirectResponse
    {
        $this->authorize('create-tastingsession', $competition);
        $this->checkCompetitionState($competition);

        $data = Request::all();
        //unset user if set to 'none'
        if (isset($data['wuser_username']) && $data['wuser_username'] === 'none') {
            unset($data['wuser_username']);
        }
        try {
            $tastingSession = $this->tastingHandler->createTastingSession($data, $competition);
        } catch (ValidationException $ve) {
            return Redirect::route('tasting.sessions/add', ['competition' => $competition->id])
                    ->withErrors($ve->getErrors())
                    ->withInput();
        }

        return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
    }

    /**
     * Show the specified tasting session.
     *
     * @param TastingSession $tastingSession
     * @return View
     */
    public function show(TastingSession $tastingSession)
    {
        $this->authorize('show-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);

        $this->shareCommonViewData($tastingSession->competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/show',
                [
                'data' => $tastingSession,
                'tasting_finished' => $this->tastingHandler->isTastingFinished($tastingSession->competition),
        ]);
    }

    /**
     * Export session results as Excel.
     *
     * @param TastingSession $tastingSession
     * @param Commission $commission
     *
     * @return BinaryFileResponse
     */
    public function exportResult(TastingSession $tastingSession, Commission $commission): BinaryFileResponse
    {
        $this->authorize('export-tastingsession-result', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);

        $wines = $tastingSession
            ->tastedwines()
            ->where('commission_id', '=', $commission->id)
            ->orderBy('tastingnumber_nr')
            ->get();
        $we = new ResultExport($wines);
        $filename = 'Kostsitzungsauswertung '
            .$tastingSession->tastingstage->id
            .'-'
            .$tastingSession->nr
            .$commission->side
            .'.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::download($we->asExcel(), $filename, $headers);
    }

    /**
     * @param TastingSession $tastingSession
     *
     * @return BinaryFileResponse
     */
    public function exportProtocol(TastingSession $tastingSession): BinaryFileResponse
    {
        $this->authorize('export-tastingsession-protocol', $tastingSession);

        $tp = new TastingProtocol($tastingSession);
        $filename = 'Kostprotokoll '.$tastingSession->tastingstage->id.'-'.$tastingSession->nr.'.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return Response::download($tp->asExcel(), $filename, $headers);
    }

    /**
     * Show update form.
     *
     * @param TastingSession $tastingSession
     * @return View
     */
    public function edit(TastingSession $tastingSession)
    {
        $this->authorize('edit-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        $this->shareCommonViewData($tastingSession->competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/form',
                [
                'data' => $tastingSession,
                'users' => $this->selectNone + User::all()->pluck('username', 'username')->all(),
        ]);
    }

    /**
     * Validate and store updated values.
     *
     * @param TastingSession $tastingSession
     *
     * @return RedirectResponse
     */
    public function update(TastingSession $tastingSession): RedirectResponse
    {
        $this->authorize('edit-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        $data = Request::all();
        try {
            $this->tastingHandler->updateTastingSession($tastingSession, $data);
        } catch (ValidationException $ve) {
            return Redirect::route('tasting.sessions/edit', ['tastingsession' => $tastingSession->id])
                    ->withErrors($ve->getErrors())
                    ->withInput();
        }

        return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
    }

    public function statistics(TastingSession $tastingSession)
    {
        $this->authorize('show-tastingsession-statistics', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);

        $this->shareCommonViewData($tastingSession->competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/statistics',
                [
                'tasting_session' => $tastingSession,
        ]);
    }

    /**
     * Show user confirmation for completing/locking tasting session.
     *
     * @param TastingSession $tastingSession
     * @return View
     */
    public function complete(TastingSession $tastingSession)
    {
        $this->authorize('lock-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        $this->shareCommonViewData($tastingSession->competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/complete', [
                'data' => $tastingSession,
        ]);
    }

    /**
     * Lock tastingstation.
     *
     * @param TastingSession $tastingSession
     *
     * @return RedirectResponse
     */
    public function lock(TastingSession $tastingSession): RedirectResponse
    {
        $this->authorize('lock-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        if (Request::has('del') && Request::input('del') == 'Ja') {
            $this->tastingHandler->lockTastingSession($tastingSession);
        }

        return Redirect::route('tasting.session/show', ['tastingsession' => $tastingSession->id]);
    }

    /**
     * Show user confirmation for deleting tasting sessions.
     *
     * @param TastingSession $tastingSession
     * @return View
     */
    public function delete(TastingSession $tastingSession)
    {
        $this->authorize('delete-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        $this->shareCommonViewData($tastingSession->competition);

        return $this->viewFactory->make('competition/tasting/tasting-session/delete')->with([
                'data' => $tastingSession,
        ]);
    }

    /**
     * Destroy database entry.
     *
     * @param TastingSession $tastingSession
     *
     * @return RedirectResponse
     */
    public function destroy(TastingSession $tastingSession): RedirectResponse
    {
        $this->authorize('delete-tastingsession', $tastingSession);
        $this->checkCompetitionState($tastingSession->competition);
        $this->checkTastingSessionLocked($tastingSession);

        $competition = $tastingSession->competition;
        if (Request::input('del') == 'Ja') {
            $this->tastingHandler->deleteTastingSession($tastingSession);
        }

        return Redirect::route('tasting.sessions', ['competition' => $competition->id]);
    }
}
