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

namespace App\MasterData;

use App\MasterData\TastingProtocol;
use App\Tasting\Taster;
use App\Tasting\TastingNumber;
use App\Tasting\TastingSession;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPExcel;
use PHPExcel_Settings;
use PHPExcel_Worksheet;
use PHPExcel_Writer_Excel5;
use function array_add;

class TastingProtocol {

	const FIRST_TASTER_COL = 3;

	/** @var TastingSession */
	private $tastingSession = null;

	/** @var Collection */
	private $commissions = null;

	/** @var Collection */
	private $tasters = null;

	/** @var array */
	private $tasterColumns = null;

	/**
	 * @param TastingSession $ts
	 */
	public function __construct(TastingSession $ts) {
		$this->tastingSession = $ts;
	}

	/**
	 * @return Collection
	 */
	private function getCommissions(): Collection {
		if (is_null($this->commissions)) {
			$this->commissions = $this->tastingSession
				->commissions()
				->with('tasters')
				->orderBy('side')
				->get();
		}
		return $this->commissions;
	}

	/**
	 * @return Collection
	 */
	private function getTasters(): Collection {
		if (is_null($this->tasters)) {
			foreach ($this->getCommissions() as $commission) {
				$tasters = $commission
					->tasters()
					->orderBy('nr')
					->get();
				if (is_null($this->tasters)) {
					$this->tasters = $tasters;
				} else {
					$this->tasters = $this->tasters->merge($tasters);
				}
			}
		}
		return $this->tasters;
	}

	/**
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setHeaders(PHPExcel_Worksheet $sheet) {
		$sheet->setCellValue('A1', 'Kostnummer');
		$sheet->setCellValue('B1', 'Dateinummer');
		$sheet->setCellValue('C1', 'Gesamtergebnis');

		$col = 3;
		foreach ($this->getTasters() as $taster) {
			$sheet->setCellValue(chr(ord('A') + $col) . '1', $taster->name);
			$col++;
		}
	}

	/**
	 * @param Taster $taster
	 * @return int
	 */
	private function getTasterColumn(Taster $taster): int {
		if (is_null($this->tasterColumns)) {
			$this->tasterColumns = [];
			//init maps
			$col = TastingProtocol::FIRST_TASTER_COL;
			$tasters = $this->getTasters();
			foreach ($tasters as $t) {
				$this->tasterColumns = array_add($this->tasterColumns, $t->id, $col);
				$col++;
			}
		}
		return $this->tasterColumns[$taster->id];
	}

	/**
	 * 
	 * @param TastingNumber $tastingnumber
	 * @return array
	 */
	private function getResults(TastingNumber $tastingnumber): array {
		$result = [];

		//find result values
		foreach ($tastingnumber->tastings as $tasting) {
			//find taster
			$currTaster = null;
			foreach ($this->getTasters() as $taster) {
				if ($tasting->taster_id === $taster->id) {
					$currTaster = $taster;
					break;
				}
			}
			if (is_null($currTaster)) {
				throw new Exception('could not find taster for tasting ' . $tasting->id);
			}
			$result[$this->getTasterColumn($currTaster)] = $tasting->rating;
		}
		return $result;
	}

	/**
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setData(PHPExcel_Worksheet $sheet) {
		$data = $this->tastingSession->tastedwines()
			->orderBy('tastingnumber_nr')
			->select('tastingnumber_nr', 'tastingnumber_id', 'wine_id', 'wine_nr', 'result')
			->get();
		$row = 2;
		foreach ($data as $wine) {
			$sheet->setCellValue('A' . $row, $wine->tastingnumber_nr);
			$sheet->setCellValue('B' . $row, $wine->wine_nr);
			$sheet->setCellValue('C' . $row, $wine->result);
			$tastingnumber = TastingNumber::find($wine->tastingnumber_id);
			$results = $this->getResults($tastingnumber);
			foreach ($results as $col => $val) {
				$sheet->setCellValue(chr(ord('A') + $col) . $row, $val);
			}
			$row++;
		}
	}

	/**
	 * @return string
	 */
	public function asExcel(): string {
		$filename = sys_get_temp_dir() . '/' . Str::random();
		$locale = 'de_DE';
		$validLocale = PHPExcel_Settings::setLocale($locale);
		if (!$validLocale) {
			Log::warning('Unable to set locale to ' . $locale . " - reverting to en_us" . PHP_EOL);
		}
		$doc = new PHPExcel();
		$sheet = $doc->getSheet();
		$sheet->setTitle('Kostprotokoll');
		$this->setHeaders($sheet);
		$this->setData($sheet);
		$writer = new PHPExcel_Writer_Excel5($doc);
		$writer->save($filename);
		return $filename;
	}

}
