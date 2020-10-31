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

namespace App\Wine;

use App\MasterData\Competition;
use App\Wine;
use FPDF;
use Illuminate\Support\Str;

class EnrollmentForm {

	/** @var Wine */
	private $wine;
	
	/** @var Competition */
	private $competition;

	public function __construct(Wine $wine) {
		$this->wine = $wine;
		$this->competition = $wine->competition;
	}

	/**
	 * @param string $text
	 */
	private function encode($text) {
		return iconv('utf-8', 'ISO-8859-2', $text);
	}

	private function addTitle(FPDF &$pdf) {
		$pdf->SetFont('Arial', 'B', 15);
		// Move to the right
		$pdf->Cell(40);
		// Title
		$pdf->Cell(10, 10, $this->encode('Weinanmeldung für Retzer Weinwoche'));
		$pdf->Ln(25);
	}

	private function addHeader(FPDF &$pdf) {
		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Write(5, $this->encode('Anliefertermine: '));

		$pdf->SetFont('Arial', '', 12);
		$pdf->Write(5,
			$this->encode('8. und 9. April 2015 laut beiliegender Aufstellung '
				. 'im Landesweingut Retz, 2 Stk. 0,75l bzw. bei kleineren 3 Flashen je Probe (umseitige'
				. ' Bedingungen beachten!)'));

		$pdf->Ln(10);

		$pdf->SetFont('Arial', 'B', 12);
		$pdf->Write(5, $this->encode('Achtung: '));

		$pdf->SetFont('Arial', '', 12);
		$pdf->Write(5,
			$this->encode('Jene Betriebe, welche schon im Vorjahr teilgenommen haben, '
				. 'brauchen kein Stammdatenblatt mehr ausfüllen, ist nur bei erstmaliger Teilnahme '
				. 'notwendig. Alle Formulare sind auch unter www.bwv-retz.at abrufbar.'));

		$pdf->Ln(10);

		$pdf->Write(5, $this->encode('Betriebsnummer: ' . $this->wine->applicant->id));

		$pdf->Ln(10);

		$pdf->Write(5, $this->encode('Familienname: ' . $this->wine->applicant->lastname));

		$pdf->Ln(10);

		$pdf->Write(5, $this->encode('Ausschankkoje: ' . $this->wine->applicant->association->name));

		$pdf->Ln(10);
	}

	private function addWineData(FPDF &$pdf) {
		$pdf->Cell(15);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(35, 10, $this->encode('bitte ankreuzen'));

		$pdf->SetFont('Arial', 'B', 12);
		foreach (['ROT', 'WEISS', 'ROSE'] as $sort) {
			$pdf->Cell(10, 10, '', 1);
			$pdf->Cell(22, 10, $this->encode($sort));
		}

		$imgPath = dirname(__FILE__) . '/wine_nr.png';
		$pdf->Image($imgPath, 160, 70, 40, 45);


		$pdf->Ln(18);
		$pdf->Cell(15);
		$pdf->SetFont('Arial', '', 12);
		foreach (['Weinviertel DAC', 'Weinviertel Reserve DAC'] as $sort) {
			$pdf->Cell(10, 10, '', 1);
			$pdf->Cell(45, 10, $this->encode($sort));
		}

		$pdf->Ln(18);

		$pdf->Write(5, $this->encode('Sorte: ' . $this->wine->winesort->name));
		$pdf->Ln(10);
		$pdf->Write(5, $this->encode('Jahrgang: ' . $this->wine->vintage));
		$pdf->Ln(10);

		$label = $this->wine->label;
		if (is_null($label) || $label === '') {
			$label = '-';
		}
		$pdf->Write(5, $this->encode('Marke: ' . $label));

		$pdf->Ln(10);
		$pdf->Write(5,
			$this->encode('Qualitätsstufe: ' . $this->wine->winequality->label
				. ' (' . $this->wine->winequality->abbr . ')'));

		$pdf->Ln(10);
		$approvalNr = $this->wine->approvalnr;
		if (is_null($approvalNr)) {
			$approvalNr = '....................';
		}
		$pdf->Write(5, $this->encode('Staatliche Prüfnummer: ' . $approvalNr));

		$commaToDot = function ($val) {
			return str_replace('.', ',', $val);
		};

		$pdf->Ln(10);
		$pdf->Cell(70, 10, 'vorhandener Alkohol: ' . $commaToDot($this->wine->alcohol) . '%vol');
		$pdf->Cell(85, 10, 'Gesamtalkohol (berechnet): ' . $commaToDot($this->wine->alcoholtot) . '%vol');
		$pdf->Cell(70, 10, 'Zucker: ' . $commaToDot($this->wine->sugar) . 'g/l');

		$pdf->Ln(20);
		$pdf->SetFont('Arial', 'B', 10);
		$pdf->Write(5,
			$this->encode('Kopie (Seite 1 und 2 genügen!) des Prüfnummernbescheides'
				. ' unbeding belegen! Bitte nicht anheften!'));

		$pdf->Ln(40);
		$pdf->SetFont('Arial', '', 10);
		$pdf->Cell(50);
		$pdf->Cell(150, 10, '..........................................................................................');
		$pdf->Ln(5);
		$pdf->Cell(80);
		$pdf->Cell(100, 10, $this->encode('Datum, Unterschrift'));
	}

	public function Save() {
		$pdf = new FPDF('P', 'mm', 'A4');
		$pdf->AddPage();
		$this->addTitle($pdf);
		$this->addHeader($pdf);
		$this->addWineData($pdf);

		$filename = sys_get_temp_dir() . '/' . Str::random();
		$pdf->Output($filename, 'F');
		return $filename;
	}

}
