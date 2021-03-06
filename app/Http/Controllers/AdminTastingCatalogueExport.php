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
 */

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminTastingCatalogueExport
{
    /**
     * All wines.
     *
     * @var Collection
     */
    private $wines;

    /**
     * Export headers.
     *
     * @var array of string
     */
    private $headers = [
        'VereinsNr',
        'Verein',
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
     * Set worksheets first rows header values.
     *
     * @param Worksheet $sheet
     */
    private function setExcelHeaders(Worksheet $sheet)
    {
        //headers
        $sheet->setCellValue('A1', $this->headers[0]);
        $sheet->setCellValue('B1', $this->headers[1]);
        $sheet->setCellValue('C1', $this->headers[2]);
        $sheet->setCellValue('D1', $this->headers[3]);
        $sheet->setCellValue('E1', $this->headers[4]);
        $sheet->setCellValue('F1', $this->headers[5]);
        $sheet->setCellValue('G1', $this->headers[6]);
        $sheet->setCellValue('H1', $this->headers[7]);
        $sheet->setCellValue('I1', $this->headers[8]);
        $sheet->setCellValue('J1', $this->headers[8]);
        $sheet->setCellValue('K1', $this->headers[8]);
    }

    /**
     * Set worksheets data rows.
     *
     * @param Worksheet $sheet
     */
    private function setExcelData(Worksheet $sheet)
    {
        // Sort data
        $this->wines = $this->wines->sort(function ($wine1, $wine2) {
            return $wine1->applicant->association->id - $wine2->applicant->association->id;
        });

        $row = 2;
        foreach ($this->wines as $w) {
            $sheet->setCellValue("A$row", $w->applicant->association->id);
            $sheet->setCellValue("B$row", $w->applicant->association->name);
            $sheet->setCellValue("C$row", $w->catalogue_number);
            $sheet->setCellValue("D$row", $w->applicant->lastname);
            $sheet->setCellValue("E$row", $w->winesort_name);
            $sheet->setCellValue("F$row", $w->label);
            $sheet->setCellValue("G$row", $w->vintage);
            $sheet->setCellValue("H$row", $w->winequality->abbr);
            if ($w->rating1) {
                $sheet->setCellValue("I$row", floor($w->rating1 * 10) / 10);
            }
            if ($w->kdb) {
                $sheet->setCellValue("J$row", 'KdB');
            }
            if ($w->sosi) {
                $sheet->setCellValue("K$row", 'SoSi');
            }
            $row++;
        }
    }

    /**
     * Constructor.
     *
     * @param Collection $wines
     */
    public function __construct(Collection $wines)
    {
        $this->wines = $wines;
    }

    /**
     * Export all wines of current competition as Excel spread sheet.
     *
     * @return string sheet
     */
    public function asExcel(): string
    {
        $filename = sys_get_temp_dir().'/'.Str::random();
        $locale = 'de_DE';
        $validLocale = Settings::setLocale($locale);
        if (! $validLocale) {
            Log::warning('Unable to set locale to '.$locale.' - reverting to en_us'.PHP_EOL);
        }
        $doc = new Spreadsheet();
        $sheet = $doc->getSheet(0);
        $layout = new PageSetup();
        $layout->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->setPageSetup($layout);
        $sheet->setTitle('Kostkatalog');
        $this->setExcelHeaders($sheet);
        $this->setExcelData($sheet);
        $writer = new Xlsx($doc);
        $writer->save($filename);

        return $filename;
    }
}
