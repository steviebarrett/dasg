<?php

//error_reporting(E_ALL); ini_set('display_errors', '1');;

require_once '../includes/include.php';
require_once '../fpdf/fpdf.php';

die("Not authorised");

/*
 * Leaving the class here for now. Move it into its own file if it grows
 */
class PDF extends FPDF
{
	function Footer()
	{
		// Go to 1.5 cm from bottom
		$this->SetY(-15);
		$this->SetFont('Arial','',12);
		// Print centered page number
		$this->Cell(0,10,$this->PageNo(),0,0,'C');
	}
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);

switch ($_GET["format"]) {
	case "author":
		//get the records by author
		$title = "Gairm Index by Author";
		$records = GairmRecords::getRecordsByAuthor();
		$pdf->SetTitle($title);
		$pdf->Cell(0,20, $title, 0, 1);
		foreach ($records as $record) {
			if ($pdf->GetY() > 280) {
				$pdf->AddPage();			//add a page if too close to the bottom margin
			}
			$pdf->SetFont('Arial','',12);
			$nameText = empty($record["firstName"]) ? $record["lastName"] : $record["lastName"] . ", " . $record["firstName"];
			$nameText = utf8_decode($nameText);
			//		$pdf->Cell(0,5, $nameText, 0, 1);
			$titleText = utf8_decode($record["title"]);
			//		$pdf->SetFont('Arial','I',12);
			//		$pdf->Cell(0,5, $titleText, 0, 1);
			//		$pdf->SetFont('Arial','',12);
			$volumeText = utf8_decode($record["volume"]);
			$yearText = "(" . utf8_decode($record["yearOfPublication"]) . ")";
			$pdf->Write(7.5, $nameText . ", ");
			$pdf->SetFont('Arial','I',12);
			$pdf->Write(7.5, $titleText);
			$pdf->SetFont('Arial','',12);
			$pdf->Write(7.5, ", " . $yearText . " vol. " . $volumeText);
			$pdf->Ln();
		}
		break;
	case "volume":
		//get the records by volume
		$title = "Gairm Index by Volume";
		$pdf->SetTitle($title);
		$records = GairmRecords::getRecordsByVolume();
		$pdf->Cell(0,20, $title, 0, 1);
		$volume = "";
		foreach ($records as $record) {
			$volumeText = utf8_decode($record["volume"]);
			if ($volumeText !== $volume) {		//print the new volume number and year
				if ($volumeText !== "1") {
					$pdf->AddPage();			//new page for each volume after Vol 1
				}
				$pdf->SetFont('Arial', 'B', 16);
				$yearText = "(" . utf8_decode($record["yearOfPublication"]) . ")";
				$pdf->Cell(0,10, "Volume " . $volumeText . " " . $yearText , 0, 1);
				$volume = $volumeText;
			}
			if ($pdf->GetY() > 280) {
				$pdf->AddPage();			//add a page if too close to the bottom margin
			}
			$pdf->SetFont('Arial','',12);
			$nameText = empty($record["firstName"]) ? $record["lastName"] : $record["lastName"] . ", " . $record["firstName"];
			$nameText = utf8_decode($nameText);
			$pdf->Write(7.5, $nameText);
			$titleText = utf8_decode($record["title"]);
			$pdf->SetFont('Arial','I',12);
			$pdf->Write(7.5, ", " . $titleText);
			$pdf->SetFont('Arial','',12);
			$pdf->Write(7.5, ", p." . $record["firstPage"]);
			$pdf->Ln();
		}
		break;
	default:
		$pdf->Cell(0,20, "Error - no format supplied", 0, 1);
}

$pdf->Output();

