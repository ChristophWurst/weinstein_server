<?php

namespace App\Http\Controllers;

use App\Contracts\TastingHandler;
use App\Exceptions\ValidationException;
use App\Tasting\Commission;
use App\Tasting\Taster;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function response;

class TasterController extends BaseController {

	/** @var TastingHandler */
	private $handler;

	/**
	 * @param TastingHandler $handler
	 */
	public function __construct(TastingHandler $handler) {
		$this->handler = $handler;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index(Request $request) {
		$commission_id = $request->get('commission_id');
		$commission = Commission::find($commission_id);
		if (is_null($commission)) {
			return response()->json([], 400);
		}

		$tastingSession = $commission->tastingSession;
		$this->authorize('list-tastingsession-tasters', $tastingSession);

		return response()->json($commission->tasters);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  Request  $request
	 * @return Response
	 */
	public function store(Request $request) {
		$commission_id = $request->get('commission_id');
		$commission = Commission::find($commission_id);
		if (is_null($commission)) {
			return response()->json([], 400);
		}

		$tastingSession = $commission->tastingSession;
		$this->authorize('add-tastingsession-taster', $tastingSession);

		$data = $request->only(['name', 'commission_id']);

		try {
			$taster = $this->handler->createTaster($data);
		} catch (ValidationException $ve) {
			return response()->json([
					'errors' => $ve->getErrors(),
					], 422);
		}
		return response()->json($taster);
	}

	/**
	 * @param Request $request
	 * @param Taster $taster
	 * @return Response
	 */
	public function update(Request $request, Taster $taster) {
		$this->authorize('edit-tastingsession-taster', $taster);

		$data = $request->only(['active', 'name']);

		try {
			$taster = $this->handler->updateTaster($taster, $data);
		} catch (ValidationException $ve) {
			return response()->json([
					'errors' => $ve->getErrors(),
					], 422);
		}
		return response()->json($taster);
	}

}
