<?php

namespace App\Http\Controllers;

use App\Contracts\WineHandler;
use App\MasterData\Competition;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use function response;

class WineApiController extends BaseController {

	/** @var WineHandler */
	private $wineHandler;

	public function __construct(WineHandler $wineHandler) {
		$this->wineHandler = $wineHandler;
	}

	/**
	 * @return Response
	 */
	public function index(Request $request) {
		$competitionId = $request->get('competition_id');
		$competition = Competition::find($competitionId);
		if (is_null($competitionId)) {
			return response()->json([], 404);
		}

		$wines = $this->wineHandler->getUsersWines(Auth::user(), $competition);
		$wines->setPath(route('wines.index', [
			'competition_id' => $competitionId,
		]));

		return response()->json($wines->toJson());
	}

}
