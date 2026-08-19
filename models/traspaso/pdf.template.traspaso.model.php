<?php
require_once __DIR__ . "/../default/pdf.template.default.listitem.model.php";
require_once __DIR__ . "/../../libs/tcpdf-sdk/vendor/autoload.php";

class PDFTemplateTraspasoModel extends TCPDF
{
  private $leftLogo;
  private $centerLogo;
  private $concept;
  private $entryNote;
  private $date;
  private $folio;

  private $originWarehouse;
  private $destinationWarehouse;

  private $outMovement;
  private $inMovement;

  private $document;

  private $products;

  private $pdf;
  private $pdfTitle;
  private $pdfFolder;

  public function __construct()
  {
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->leftLogo             = ADM_FAVICON;
    $this->centerLogo           = ADM_LOGO_WIDTH_LARGE;
    $this->concept              = "";
    $this->entryNote            = "";
    $this->date                 = "";
    $this->folio                = "";
    $this->originWarehouse      = "";
    $this->destinationWarehouse = "";
    $this->outMovement          = "";
    $this->inMovement           = "";
    $this->document             = "";
    $this->products             = [];

    $datetime                   = date("YmdHis");
    $this->pdfTitle             = "pdf-{$datetime}";
    $this->pdfFolder            = "";
  }

  /**
   * Getters
   */
  public function getLeftLogo()
  {
    return $this->leftLogo;
  }

  public function getCenterLogo()
  {
    return $this->centerLogo;
  }

  public function getConcept()
  {
    return $this->concept;
  }

  public function getEntryNote()
  {
    return $this->entryNote;
  }

  public function getDate()
  {
    return $this->date;
  }

  public function getFolio()
  {
    return $this->folio;
  }

  public function getOriginWarehouse()
  {
    return $this->originWarehouse;
  }

  public function getDestinationWarehouse()
  {
    return $this->destinationWarehouse;
  }

  public function getOutMovement()
  {
    return $this->outMovement;
  }

  public function getInMovement()
  {
    return $this->inMovement;
  }

  public function getDocument()
  {
    return $this->document;
  }

  public function getProducts()
  {
    return $this->products;
  }

  public function getPdfTitle()
  {
    return $this->pdfTitle;
  }

  public function getPdfFolder()
  {
    return $this->pdfFolder;
  }

  /**
   * Setters
   */
  public function setLeftLogo($logo)
  {
    $this->leftLogo = $logo;
  }

  public function setCenterLogo($logo)
  {
    $this->centerLogo = $logo;
  }

  public function setConcept($concept)
  {
    $this->concept = $concept;
  }

  public function setEntryNote($entryNote)
  {
    $this->entryNote = $entryNote;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function setFolio($folio)
  {
    $this->folio = $folio;
  }

  public function setOriginWarehouse($originWarehouse)
  {
    $this->originWarehouse = $originWarehouse;
  }

  public function setDestinationWarehouse($destinationWarehouse)
  {
    $this->destinationWarehouse = $destinationWarehouse;
  }

  public function setOutMovement($outMovement)
  {
    $this->outMovement = $outMovement;
  }

  public function setInMovement($inMovement)
  {
    $this->inMovement = $inMovement;
  }

  public function setDocument($document)
  {
    $this->document = $document;
  }

  public function setProducts($products)
  {
    $this->products = $products;
  }

  public function setPdfTitle($pdfTitle)
  {
    $this->pdfTitle = $pdfTitle;
  }

  public function setPdfFolder($pdfFolder)
  {
    $this->pdfFolder = $pdfFolder;
  }

  /**
   * Another methods
   */
  public function Header()
  {
    // Obtener el header
    ob_start();
    include __DIR__ . "/html/header.php";
    $header = ob_get_clean();

    $this->setY(5);
    $this->writeHTML($header, true, false, true, false, '');
  }

  // Page footer
  public function Footer()
  {
    // poner una imagen que ocupe el 100% de ancho en el footer
    $this->setY(-25);
    //$this->Image(BASE_URL . "/src/assets/images/pdf-footer.jpeg", 0, $this->getY(), 210, 18, '', '', '', false, 300, '', false, false, 0, false, false, false);

    $this->SetFont('helvetica', '', 9);
    $this->Ln(4);
    $this->Cell(95, 6, '______________________________________________', 0, 0, 'L');
    $this->Cell(0, 6, '______________________________________________', 0, 0, 'L');

    $this->Ln(5);
    $this->Cell(80, 6, 'Envió', 0, 0, 'C');
    $this->Cell(115, 6, 'Recibió', 0, 0, 'C');
  }

  public function createPDF()
  {
    $this->SetMargins(PDF_MARGIN_LEFT, 30, PDF_MARGIN_RIGHT);
    $this->SetTitle($this->pdfTitle);

    $this->AddPage();
    $this->SetFont('helvetica', '', 12);

    ob_start();
    include __DIR__ . "/html/body.php";
    $html = ob_get_clean();

    $this->writeHTML($html, true, false, true, false, '');
  }

  public function showPDF()
  {
    $this->Output("{$this->pdfTitle}.pdf", 'I');
  }

  public function savePDF()
  {
    $this->Output("{$this->pdfFolder}/{$this->pdfTitle}.pdf", 'F');
  }

  public function deletePDF()
  {
    unlink("{$this->pdfFolder}/{$this->pdfTitle}.pdf");
  }
}
