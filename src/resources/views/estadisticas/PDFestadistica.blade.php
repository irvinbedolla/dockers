<?php

require_once('FPDI/src/Fpdf.php');
require_once('FPDI/src/autoload.php');

$pdf = new \setasign\Fpdi\Fpdi();

$pageCount = $pdf->setSourceFile('public/PDFS/Aviso_de_Privacidad_Integral.pdf');
$pageId = $pdf->importPage(1, \setasign\Fpdi\PdfReader\PageBoundaries::MEDIA_BOX);

$pdf->addPage();
$pdf->useImportedPage($pageId, 10, 10, 90);

$pdf->Output('I', 'generated.pdf');

<?php
require('fpdf/fpdf.php');

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(40,10,'¡Hola, Mundo!');
$pdf->Output();
?>