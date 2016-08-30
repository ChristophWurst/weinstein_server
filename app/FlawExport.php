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

namespace App;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPExcel;
use PHPExcel_Settings;
use PHPExcel_Worksheet;
use PHPExcel_Worksheet_PageSetup;
use PHPExcel_Writer_Excel5;

class FlawExport {

	private $wines;
	private $headers = [
		'DateiNr',
		'Sorte',
		'Qualität',
		'Marke',
		'Jahr',
		'BetriebsNr',
		'Betriebsbezeichnung',
		'Titel',
		'Vorname',
		'Nachname',
		'Weinstand',
		'1. Bewertung',
		'Kommentar',
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
		$sheet->setCellValue("J1", $this->headers[9]);
		$sheet->setCellValue("K1", $this->headers[10]);
		$sheet->setCellValue("L1", $this->headers[11]);
		$sheet->setCellValue("M1", $this->headers[12]);
	}

	/**
	 * Set worksheets data rows
	 * 
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setExcelData(PHPExcel_Worksheet $sheet) {
		//data
		$row = 2;
		foreach ($this->wines as $w) {
			$sheet->setCellValue("A$row", $w->nr);
			$sheet->setCellValue("B$row", $w->winesort_name);
			$sheet->setCellValue("C$row", $w->quality_label);
			$sheet->setCellValue("D$row", $w->label);
			$sheet->setCellValue("E$row", $w->vintage);
			$sheet->setCellValue("F$row", $w->applicant->id);
			$sheet->setCellValue("G$row", $w->applicant->label);
			$sheet->setCellValue("H$row", $w->applicant->title);
			$sheet->setCellValue("I$row", $w->applicant->firstname);
			$sheet->setCellValue("J$row", $w->applicant->lastname);
			$sheet->setCellValue("K$row", $w->applicant->association->name);
			if ($w->rating1) {
				$sheet->setCellValue("L$row", $w->rating1);
			}
			$sheet->setCellValue("M$row", $w->comment);
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
		$sheet->setTitle('Fehlerprotokoll');
		$this->setExcelHeaders($sheet);
		$this->setExcelData($sheet);
		$writer = new PHPExcel_Writer_Excel5($doc);
		$writer->save($filename);
		return $filename;
	}

}
