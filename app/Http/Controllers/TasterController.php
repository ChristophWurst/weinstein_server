<?php

namespace App\Http\Controllers;

use App\Contracts\TastingHandler;
use App\Exceptions\IllegalTastingStageException;
use App\Exceptions\ValidationException;
use App\MasterData\Competition;
use App\MasterData\CompetitionState;
use App\Tasting\Commission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function response;

class TasterController extends BaseController {

	/** @var TastingHandler */
	private $handler;

	public function __construct(TastingHandler $handler) {
		$this->handler = $handler;
	}

	private function checkCompetitionState(Competition $competition) {
		// TODO: might make sense to move to BL layer
		if (!$competition->competitionState->is(CompetitionState::STATE_TASTING1) || !$competition->competitionState->is(CompetitionState::STATE_TASTING1)) {
			throw new IllegalTastingStageException();
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Commission $commission) {
		$this->authorize('list-tastingsession-tasters');
		$this->checkCompetitionState($commission->tastingSession->competition);

		return response()->json($commission->tasters);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request, Commission $commission) {
		$this->authorize('list-tastingsession-tasters');
		$this->checkCompetitionState($commission->tastingSession->competition);
	
		$data = $request->only(['name']);

		try {
			$taster = $this->handler->addTasterToCommission($data, $commission);
		} catch (ValidationException $ve) {
			return response()->json([
				'errors' => $ve->getErrors(),
			], 422);
		}
		return response()->json($taster);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  Request  $request
	 * @param  int  $id
	 * @return Response
	 */
	public function update(Request $request, $id) {
		//
	}

}
