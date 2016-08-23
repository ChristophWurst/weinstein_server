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
 *
 */

namespace Weinstein\Competition\TastingSession\Tasting;

use App;
use App\MasterData\Competition;
use App\Tasting\Commission;
use App\Tasting\TastedWine;
use App\Tasting\Tasting;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TastingHandler {

	/**
	 * Tasting data provider
	 * 
	 * @var TastingDataProvider 
	 */
	private $dataProvider;

	/**
	 * Constructor
	 * 
	 * @param TastingDataProvider $dataProvider
	 */
	public function __construct(TastingDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Create a new tasting
	 * 
	 * @param array $data
	 * @param TastingSession $tastingSession
	 */
	public function create(array $data, TastingSession $tastingSession) {
		$nrCommissions = count($this->getNextTastingNumbers($tastingSession));
		$validator = new TastingValidator($data);
		$validator->setTastingSession($tastingSession);
		$validator->setNrOfCommissions($nrCommissions);
		$validator->validateCreate();

		foreach ($tastingSession->commissions as $commission) {
			if (($commission->side === 'b') && ($nrCommissions == 1)) {
				continue;
			}
			if ($commission->side === 'a') {
				$tastingNumber = $data['tastingnumber_id1'];
			} elseif ($commission->side === 'b') {
				$tastingNumber = $data['tastingnumber_id2'];
			} else {
				Log::error('Invalid commission side ' . $commission->side);
				App::abort(500);
			}

			/**
			 * store all tasters ratings
			 * 
			 * ratings are divided by 10 because the input range is 10 to 50
			 * because that is easier to enter
			 */
			foreach ($commission->tasters()->active()->get() as $taster) {
				Tasting::create(array(
				    'taster_id' => $taster->id,
				    'tastingnumber_id' => $tastingNumber,
				    'rating' => $data[$commission->side . $taster->nr] / 10,
				));
			}

			/*
			 * Store comment
			 */
			$tastingNumber = TastingNumber::find($tastingNumber);
			$wine = $tastingNumber->wine;
			$wine->comment = $data['comment-' . $commission->side];
			$wine->save();
		}
	}

	/**
	 * Update all tasting for the given tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @param array $data
	 * @param Commission $commission
	 */
	public function update(TastingNumber $tastingNumber, array $data, TastingSession $tastingSession, Commission $commission) {
		$validator = new TastingValidator($data, $tastingNumber);
		$validator->setTastingSession($tastingSession);
		$validator->setCommission($commission);
		$validator->validateUpdate();

		DB::beginTransaction();
		//delete existing tastings
		$tastingNumber->tastings()->delete();

		/**
		 * store all tasters ratings
		 * 
		 * ratings are divided by 10 because the input range is 10 to 50
		 * because that is easier to enter
		 */
		foreach ($commission->tasters as $taster) {
			Tasting::create(array(
			    'taster_id' => $taster->id,
			    'tastingnumber_id' => $tastingNumber->id,
			    'rating' => $data[$commission->side . $taster->nr] / 10,
			));
		}

		/*
		 * Store comment
		 */
		$wine = $tastingNumber->wine;
		$wine->comment = $data['comment'];
		$wine->save();
		DB::commit();
	}

	/**
	 * Delete the tasting
	 * 
	 * @param Tasting $tasting
	 */
	public function delete(Tasting $tasting) {
		throw new Exception('deleting tastings is not allowed');
	}

	/**
	 * Check if given tastingnumber has already been tasted
	 * 
	 * @param TastingNumber $tastingNumber
	 * @return boolean
	 */
	public function isTasted(TastingNumber $tastingNumber) {
		return TastedWine::where('tastingnumber_id', $tastingNumber->id)->count() > 0;
	}

	/**
	 * Get all tastings for competition and/or tasting session
	 * 
	 * @param Competition $competition
	 * @param \Weinstein\Competition\TastingSession\Tasting\TastingSession $tastingsession
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingSession $tastingsession = null) {
		return $this->dataProvider->getAll($competition, $tastingsession);
	}

	/**
	 * Get next 1 or 2 tasting numbers
	 * 
	 * @param TastingSession $tastingSession
	 * @return array
	 */
	public function getNextTastingNumbers(TastingSession $tastingSession) {
		$tastingNumbers = $this->getUntasted($tastingSession->competition, $tastingSession->competition->getTastingStage(), 2);
		$data = array();
		if ($tastingNumbers->count() > 0) {
			$data['a'] = $tastingNumbers->get(0);
		}
		if ($tastingNumbers->count() > 1 && $tastingSession->commissions()->count() > 1) {
			$data['b'] = $tastingNumbers->get(1);
		}
		return $data;
	}

}
