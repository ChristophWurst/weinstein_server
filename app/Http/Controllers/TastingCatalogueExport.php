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

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PHPExcel;
use PHPExcel_Settings;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Writer_Excel5;

class TastingCatalogueExport {

	/**
	 * All wines
	 * 
	 * @var Collection
	 */
	private $wines;

	/**
	 * Export headers
	 * 
	 * @var array of string
	 */
	private $headers = [
		'KatNr',
		'Name',
		'Sorte',
		'Marke',
		'Jahr',
		'Qualität',
		'Bewertung',
		'KdB',
		'SoSi',
	];

	/**
	 * Set worksheets first rows header values
	 * 
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setExcelHeaders(PHPExcel_Worksheet $sheet) {
		//headers
		$sheet->setCellValue("A1", $this->headers[0]);
		$sheet->setCellValue("B1", $this->headers[1]);
		$sheet->setCellValue("C1", $this->headers[2]);
		$sheet->setCellValue("D1", $this->headers[3]);
		$sheet->setCellValue("E1", $this->headers[4]);
		$sheet->setCellValue("F1", $this->headers[5]);
		$sheet->setCellValue("G1", $this->headers[6]);
		$sheet->setCellValue("H1", $this->headers[7]);
		$sheet->setCellValue("I1", $this->headers[8]);
	}

	/**
	 * Set worksheets data rows
	 * 
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setExcelData(PHPExcel_Worksheet $sheet) {
		// Sort data
		$this->wines = $this->wines->sort(function($wine1, $wine2) {
			return $wine1->applicant->association->id - $wine2->applicant->association->id;
		});

		$row = 2;
		foreach ($this->wines as $w) {
			$sheet->setCellValue("A$row", $w->catalogue_number);
			$sheet->setCellValue("B$row", $w->applicant->lastname);
			$sheet->setCellValue("C$row", $w->winesort_name);
			$sheet->setCellValue("D$row", $w->label);
			$sheet->setCellValue("E$row", $w->vintage);
			$sheet->setCellValue("F$row", $w->winequality->abbr);
			if ($w->rating1) {
				$sheet->setCellValue("G$row", floor($w->rating1 * 10) / 10);
			}
			if ($w->kdb) {
				$sheet->setCellValue("H$row", "KdB");
			}
			if ($w->sosi) {
				$sheet->setCellValue("I$row", "SoSi");
			}
			$row++;
		}
	}

	/**
	 * Constructor
	 * 
	 * @param Collection $wines
	 */
	public function __construct(Collection $wines) {
		$this->wines = $wines;
	}

	/**
	 * Export all wines of current competition as Excel spread sheet
	 * 
	 * @return Excel sheet
	 */
	public function asExcel() {
		$filename = sys_get_temp_dir() . '/' . Str::random();
		$locale = 'de_DE';
		$validLocale = PHPExcel_Settings::setLocale($locale);
		if (!$validLocale) {
			Log::warning('Unable to set locale to ' . $locale . " - reverting to en_us" . PHP_EOL);
		}
		$doc = new PHPExcel();
		$sheet = $doc->getSheet();
		$layout = new PHPExcel_Worksheet_PageSetup();
		$layout->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		$sheet->setPageSetup($layout);
		$sheet->setTitle('Kostkatalog');
		$this->setExcelHeaders($sheet);
		$this->setExcelData($sheet);
		$writer = new PHPExcel_Writer_Excel5($doc);
		$writer->save($filename);
		return $filename;
	}

}
