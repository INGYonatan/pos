<?php
class CUSTOM_TCPDF extends TCPDF
{
  public $bankLogo;
  public $bankAccountNumber;
  public $bankClabe;
  // Page footer
  public function Footer()
  {
    $this->SetY(-16);
    $this->SetFont('helvetica', 'I', 7);

    // Logo a la izquierda
    $this->Image($this->bankLogo, 15, $this->GetY() + 1, 30, 0, '', '', 'T', false, 300, '', false, false, 0, false, false, false);

    // Datos bancarios al centro
    $this->SetX(60);
    $this->Cell(90, 3, 'GRUPO FINANCIERO PAAL S.A. DE C.V.', 0, 1, 'C');
    $this->SetX(60);
    $this->Cell(90, 3, 'GFP140325SX0', 0, 1, 'C');

    // Datos al derecha
    $this->SetY($this->GetY() - 6);
    $this->SetX(150);
    $this->Cell(45, 3, 'NO. DE CUENTA: ' . $this->bankAccountNumber, 0, 1, 'R');
    $this->SetX(150);
    $this->Cell(45, 3, 'CLABE: ' . $this->bankClabe, 0, 0, 'R');
  }
}

class QuotePDF
{
  public $quote;
  private $pdf;

  public function __construct(
    $quote
  ) {
    $this->quote = $quote;
    $this->pdf;
  }

  public function createPDF()
  {
    $quote    = $this->quote;
    $company  = $quote->company;
    $seller   = $quote->seller;
    $customer = $quote->customer;
    $products = $quote->products;
    $bank     = $quote->bank;

    $pdf = new CUSTOM_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, 31);

    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(ADM_NAME);
    $pdf->SetTitle('COTIZACIÓN_' . $quote->folio);

    $pdf->setPrintHeader(false);
    //$pdf->setPrintFooter(false);

    $pdf->AddPage();

    $pdf->SetFont('helvetica', '', 12);

    $pdf->bankLogo          = $bank->logo;
    $pdf->bankAccountNumber = $bank->accountNumber;
    $pdf->bankClabe         = $bank->clabe;

    ob_start();
    include 'html/page01.php';
    $page01 = ob_get_clean();

    $pdf->writeHTML($page01, true, false, true, false, '');


    /* $pdf->AddPage();

    ob_start();
    include 'html/page02.php';
    $page02 = ob_get_clean();

    $pdf->writeHTML($page02, true, false, true, false, ''); */

    $this->pdf = $pdf;
  }

  public function downloadPDF()
  {
    $pdf    = $this->pdf;
    $quote  = $this->quote;

    $pdf->Output('COTIZACION_' . $quote->folio . '.pdf', 'D');
  }

  public function showPDF()
  {
    $pdf    = $this->pdf;
    $quote  = $this->quote;

    $pdf->Output('COTIZACION_' . $quote->folio . '.pdf', 'I');
  }
}
