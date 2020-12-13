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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class WebCatalogueExport
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
        'dateinr',
        'katalognr',
        'betriebsnr',
        'bezeichnung',
        'titel',
        'vorname',
        'nachname',
        'adresse',
        'plz',
        'ort',
        'telnr',
        'faxnr',
        'mobil',
        'e-mail',
        'internetadresse',
        'weinbauvereinnr',
        'weinbauverein',
        'sortenr',
        'sorte',
        'marke',
        'jahrgang',
        'qunr',
        'qu',
        'punkte',
        'kreisderbesten',
        'sortensieger',
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
        $sheet->setCellValue('J1', $this->headers[9]);
        $sheet->setCellValue('K1', $this->headers[10]);
        $sheet->setCellValue('L1', $this->headers[11]);
        $sheet->setCellValue('M1', $this->headers[12]);
        $sheet->setCellValue('N1', $this->headers[13]);
        $sheet->setCellValue('O1', $this->headers[14]);
        $sheet->setCellValue('P1', $this->headers[15]);
        $sheet->setCellValue('Q1', $this->headers[16]);
        $sheet->setCellValue('R1', $this->headers[17]);
        $sheet->setCellValue('S1', $this->headers[18]);
        $sheet->setCellValue('T1', $this->headers[19]);
        $sheet->setCellValue('U1', $this->headers[20]);
        $sheet->setCellValue('V1', $this->headers[21]);
        $sheet->setCellValue('W1', $this->headers[22]);
        $sheet->setCellValue('X1', $this->headers[23]);
        $sheet->setCellValue('Y1', $this->headers[24]);
        $sheet->setCellValue('Z1', $this->headers[25]);
    }

    /**
     * Set worksheets data rows.
     *
     * @param Worksheet $sheet
     */
    private function setExcelData(Worksheet $sheet)
    {
        //data
        $row = 2;
        // Sort data
        $this->wines = $this->wines->sort(function ($wine1, $wine2) {
            return $wine1->applicant->association->id - $wine2->applicant->association->id;
        });

        foreach ($this->wines as $w) {
            $sheet->setCellValue("A$row", $w->nr);
            $sheet->setCellValue("B$row", $w->catalogue_number);
            $sheet->setCellValue("C$row", $w->applicant->id);
            $sheet->setCellValue("D$row", $w->applicant->label);
            $sheet->setCellValue("E$row", $w->applicant->title);
            $sheet->setCellValue("F$row", $w->applicant->firstname);
            $sheet->setCellValue("G$row", $w->applicant->lastname);
            $sheet->setCellValue("H$row", $w->applicant->address->street.' '.$w->applicant->address->nr);
            $sheet->setCellValue("I$row", $w->applicant->address->zipcode);
            $sheet->setCellValue("J$row", $w->applicant->address->city);
            $sheet->setCellValueExplicit("K$row", $w->applicant->phone, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("L$row", $w->applicant->fax, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("M$row", $w->applicant->mobile, DataType::TYPE_STRING);
            $sheet->setCellValue("N$row", $w->applicant->email);
            $sheet->setCellValue("O$row", $w->applicant->web);
            $sheet->setCellValue("P$row", $w->applicant->association->id);
            $sheet->setCellValue("Q$row", $w->applicant->association->name);
            $sheet->setCellValue("R$row", $w->winesort->order);
            $sheet->setCellValue("S$row", $w->winesort->name);
            $sheet->setCellValue("T$row", $w->label);
            $sheet->setCellValue("U$row", $w->vintage);
            if ($w->winequality) {
                $sheet->setCellValue("V$row", $w->winequality->id);
                $sheet->setCellValue("W$row", $w->winequality->abbr);
            }
            $sheet->setCellValue("X$row", floor($w->rating1 * 10) / 10);
            $sheet->setCellValue("Y$row", $w->kdb ? 1 : 0);
            $sheet->setCellValue("Z$row", $w->sosi ? 1 : 0);

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
        $writer = new Xls($doc);
        $writer->save($filename);

        return $filename;
    }
}
