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

namespace Weinstein\Competition\TastingNumber;

use App\MasterData\Competition;
use App\Tasting\TastingNumber;
use App\Tasting\TastingStage;
use App\Wine;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use PHPExcel_IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Exceptions\ValidationException;

class TastingNumberHandler {

	/**
	 * Data provider
	 * 
	 * @var TastingNumberDataProvider
	 */
	private $dataProvider;

	/**
	 * Constructor
	 * 
	 * @param TastingNumberDataProvider $dataProvider
	 */
	public function __construct(TastingNumberDataProvider $dataProvider) {
		$this->dataProvider = $dataProvider;
	}

	/**
	 * Create a new tasting number
	 * 
	 * @param array $data
	 * @param Competition $competition
	 * @return TastingNumber
	 */
	public function create(array $data, Competition $competition) {
		$validator = new TastingNumberValidator($data);
		$validator->setCompetition($competition);
		$validator->validateCreate();

		//get wine by its nr
		$wine = Wine::where('competition_id', $competition->id)
			->where('nr', $data['wine_nr'])
			->first();
		$data['wine_id'] = $wine->id;
		//competitions tasting stage is choosen by default
		$data['tastingstage_id'] = $competition->getTastingStage()->id;

		return TastingNumber::create($data);
	}

	/**
	 * Update the tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 * @param array $data
	 * @return TastingNumber
	 */
	public function update(TastingNumber $tastingNumber, array $data) {
		return $tastingNumber->update($data);
	}

	/**
	 * Delete the tasting number
	 * 
	 * @param TastingNumber $tastingNumber
	 */
	public function delete(TastingNumber $tastingNumber) {
		$tastingNumber->delete();
	}

	/**
	 * Import tasting numbers using a file
	 * 
	 * @param UploadedFile $file
	 * @param Competition $competition
	 * @return int Number of read lines
	 */
	public function import(UploadedFile $file, Competition $competition) {
		//iterate over all entries and try to store them
		//if exceptions occur, all db actions are rolled back to prevent data 
		//inconsistency
		try {
			$doc = PHPExcel_IOFactory::load($file->getRealPath());
		} catch (Exception $ex) {
			throw new ValidationException(new MessageBag(array('Ung&uuml;ltiges Dateiformat')));
		}

		$sheet = $doc->getActiveSheet();

		DB::beginTransaction();
		try {
			$rowCount = 1;

			foreach ($sheet->toArray() as $row) {
				if (!isset($row[0]) || !isset($row[1])) {
					Log::error('invalid tasting number import format');
					throw new ValidationException(new MessageBag(array('Fehler beim Lesen der Datei')));
				}
				$data = array(
				    'nr' => $row[0],
				    'wine_nr' => $row[1],
				);
				$this->create($data, $competition);
				$rowCount++;
			}
		} catch (ValidationException $ve) {
			DB::rollback();
			$messages = new MessageBag(array(
			    'row' => 'Fehler in Zeile ' . $rowCount,
			));
			$messages->merge($ve->getErrors());
			throw new ValidationException($messages);
		}
		DB::commit();
		//return number of read lines
		return $rowCount - 1;
	}

	/**
	 * Get competitions tasting numbers
	 * 
	 * if no valid competition is given, all tasting numbers are returned
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @return Collection
	 */
	public function getAll(Competition $competition = null, TastingStage $tastingStage = null) {
		return $this->dataProvider->getAll($competition, $tastingStage);
	}

	/**
	 * Get untasted tasting numbers
	 * 
	 * @param Competition $competition
	 * @param TastingStage $tastingStage
	 * @param int $limit
	 * @return Collection
	 */
	public function getUntasted(Competition $competition, TastingStage $tastingStage = null, $limit = null) {
		return $this->dataProvider->getUntasted($competition, $tastingStage, $limit);
	}

}
