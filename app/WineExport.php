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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpSpreadsheet\Worksheet\PageSetup;;
use PhpSpreadsheet\Writer\Xls;

class WineExport {

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
		'DateiNr',
		'SortenNr',
		'Sorte',
		'QualNr',
		'Qualität',
		'Marke',
		'Jahr',
		'BetriebsNr',
		'Betriebsbezeichnung',
		'Titel',
		'Vorname',
		'Nachname',
		'Telefon',
		'Mobil',
		'E-Mail',
		'Adresse',
		'WeinstandNr',
		'Weinstand',
		'Alkohol',
		'Alkohol ges.',
		'Zucker',
		'1. Bewertung',
		'2. Bewertung',
		'KdB',
		'SoSi',
		'Ex',
		'Ausschank',
	];
	/**
	 * @var bool
	 */
	private $showTasting2;

	/**
	 * Set worksheets first rows header values
	 * 
	 * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $sheet
	 */
	private function setExcelHeaders(\PhpOffice\PhpSpreadsheet\Spreadsheet $sheet) {
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
		$sheet->setCellValue("N1", $this->headers[13]);
		$sheet->setCellValue("O1", $this->headers[14]);
		$sheet->setCellValue("P1", $this->headers[15]);
		$sheet->setCellValue("Q1", $this->headers[16]);
		$sheet->setCellValue("R1", $this->headers[17]);
		$sheet->setCellValue("S1", $this->headers[18]);
		$sheet->setCellValue("T1", $this->headers[19]);
		$sheet->setCellValue("U1", $this->headers[20]);
		$sheet->setCellValue("V1", $this->headers[21]);
		$sheet->setCellValue("W1", $this->headers[22]);
		$sheet->setCellValue("X1", $this->headers[23]);
		$sheet->setCellValue("Y1", $this->headers[24]);
		$sheet->setCellValue("Z1", $this->headers[25]);
		$sheet->setCellValue("AA1", $this->headers[26]);
	}

	/**
	 * Set worksheets data rows
	 * 
	 * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $sheet
	 */
	private function setExcelData(\PhpOffice\PhpSpreadsheet\Spreadsheet $sheet) {
		//data
		$row = 2;
		foreach ($this->wines as $w) {
			$sheet->setCellValue("A$row", $w->nr);
			$sheet->setCellValue("B$row", $w->winesort_order);
			$sheet->setCellValue("C$row", $w->winesort_name);
			$sheet->setCellValue("D$row", $w->quality_id);
			$sheet->setCellValue("E$row", $w->quality_label);
			$sheet->setCellValue("F$row", $w->label);
			$sheet->setCellValue("G$row", $w->vintage);
			$sheet->setCellValue("H$row", $w->applicant->id);
			$sheet->setCellValue("I$row", $w->applicant->label);
			$sheet->setCellValue("J$row", $w->applicant->title);
			$sheet->setCellValue("K$row", $w->applicant->firstname);
			$sheet->setCellValue("L$row", $w->applicant->lastname);
			$sheet->setCellValue("M$row", $w->applicant->phone);
			$sheet->getCell("M$row")->setDataType(DataType::TYPE_STRING);
			$sheet->setCellValue("N$row", $w->applicant->mobile);
			$sheet->getCell("N$row")->setDataType(DataType::TYPE_STRING);
			$sheet->setCellValue("O$row", $w->applicant->email);
			$address = $w->applicant->address->zipcode
				. ' ' . $w->applicant->address->city
				. ', ' . $w->applicant->address->street
				. ' ' . $w->applicant->address->nr;
			$sheet->setCellValue("P$row", $address);
			$sheet->setCellValue("Q$row", $w->applicant->association->id);
			$sheet->setCellValue("R$row", $w->applicant->association->name);
			$sheet->setCellValue("S$row", $w->alcohol);
			$sheet->setCellValue("T$row", $w->alcoholtot);
			$sheet->setCellValue("U$row", $w->sugar);
			if ($w->rating1) {
				$sheet->setCellValue("V$row", $w->rating1);
			}
			if ($this->showTasting2 && $w->rating2) {
				$sheet->setCellValue("W$row", $w->rating2);
			}
			if ($w->kdb) {
				$sheet->setCellValue("X$row", "ja");
			}
			if ($w->sosi) {
				$sheet->setCellValue("Y$row", "ja");
			}
			if ($w->excluded) {
				$sheet->setCellValue("Z$row", "ja");
			}
			if ($w->chosen) {
				$sheet->setCellValue("AA$row", "ja");
			}
			$row++;
		}
	}

	/**
	 * Constructor
	 *
	 * @param Collection $wines
	 */
	public function __construct(Collection $wines, bool $showTasting2 = true) {
		$this->wines = $wines;
		$this->showTasting2 = $showTasting2;
	}

	/**
	 * Export all wines of current competition as Excel spread sheet
	 * 
	 * @return Excel sheet
	 */
	public function asExcel() {
		$filename = sys_get_temp_dir() . '/' . Str::random();
		$locale = 'de_DE';
		$validLocale = Settings::setLocale($locale);
		if (!$validLocale) {
			Log::warning('Unable to set locale to ' . $locale . " - reverting to en_us" . PHP_EOL);
		}
		$doc = new Spreadsheet();
		$sheet = $doc->getSheet(0);
		$layout = new PageSetup();
		$layout->setPaperSize(PageSetup::PAPERSIZE_A4);
		$sheet->setPageSetup($layout);
		$sheet->setTitle('Weine');
		$this->setExcelHeaders($sheet);
		$this->setExcelData($sheet);
		$writer = new Xls($doc);
		$writer->save($filename);
		return $filename;
	}

}
