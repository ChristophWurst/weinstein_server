<?php

namespace App\Http\Controllers;

use App\Contracts\WineHandler;
use App\Exceptions\InvalidCompetitionStateException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\Wine;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use function response;
use function route;

class WineApiController extends BaseController
{
    /** @var WineHandler */
    private $wineHandler;

    public function __construct(WineHandler $wineHandler)
    {
        $this->wineHandler = $wineHandler;
    }

    /**
     * @return JsonResponse|Paginator
     */
    public function index(Request $request)
    {
        $competitionId = $request->get('competition_id');
        $competition = Competition::find($competitionId);
        if (is_null($competitionId)) {
            return response()->json([], 404);
        }

        $wines = $this->wineHandler->getUsersWines(Auth::user(), $competition);
        $wines->setPath(route('wines.index', [
            'competition_id' => $competitionId,
        ]));

        return $wines;
    }

    public function update(Wine $wines, Request $request)
    {
        $oldData = $wines->toArray();
        $data = array_merge($oldData,
            [
            'kdb' => $request->get('kdb'),
            'sosi' => $request->get('sosi'),
            'excluded' => $request->get('excluded'),
            'chosen' => $request->get('chosen'),
        ]);
        $this->authorize('update-wine', [
            $wines,
            $data,
        ]);

        try {
            return $this->wineHandler->update($wines, $data);
        } catch (ValidationException $ex) {
            return response()->json(['errors' => $ex->getErrors()], 412);
        } catch (AuthorizationException $ex) {
            return response()->json(['error' => $ex->getMessage()], 400);
        } catch (InvalidCompetitionStateException $ex) {
            return response()->json(['error' => 'Diese Änderung ist in diesem Abschnitt der Verkostung nicht erlaubt.'], 400);
        }
    }
}
