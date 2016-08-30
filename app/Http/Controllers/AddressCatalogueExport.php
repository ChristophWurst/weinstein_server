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

class AddressCatalogueExport {

	/**
	 * All addresses
	 * 
	 * @var Collection
	 */
	private $addresses;

	/**
	 * Export headers
	 * 
	 * @var array of string
	 */
	private $headers = [
		'StandNr',
		'Betrieb',
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
	}

	/**
	 * Set worksheets data rows
	 * 
	 * @param PHPExcel_Worksheet $sheet
	 */
	private function setExcelData(PHPExcel_Worksheet $sheet) {
		//data
		$row = 2;
		foreach ($this->addresses as $address) {
			$sheet->setCellValue("A$row", $address->association_id);
			$sheet->setCellValue("B$row", $address->data);
			$row++;
		}
	}

	/**
	 * Constructor
	 * 
	 * @param Collection $addresses
	 */
	public function __construct(Collection $addresses) {
		$this->addresses = $addresses;
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
		$sheet->setTitle('Adresskatalog');
		$this->setExcelHeaders($sheet);
		$this->setExcelData($sheet);
		$writer = new PHPExcel_Writer_Excel5($doc);
		$writer->save($filename);
		return $filename;
	}

}
