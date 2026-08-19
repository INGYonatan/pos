<?php
require_once __DIR__ . "/pdf.template.default.listitem.model.php";
require_once __DIR__ . "/../../libs/tcpdf-sdk/vendor/autoload.php";

class PDFTemplateDefaultModel extends TCPDF
{
  private $leftLogo;
  private $centerLogo;
  private $type;
  private $concept;
  private $entryNote;
  private $date;
  private $warehouse;

  private $products;

  private $pdf;
  private $pdfTitle;
  private $pdfFolder;

  public function __construct()
  {
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->leftLogo   = BASE_URL . "/src/assets/images/pdf-template-icon.png";
    $this->centerLogo = BASE_URL . "/src/assets/images/pdf-template-logo-name.png";
    $this->type       = "default";
    $this->concept    = "";
    $this->entryNote  = "";
    $this->date       = "";
    $this->warehouse  = "";
    $this->products   = [];

    $datetime         = date("YmdHis");
    $this->pdfTitle   = "pdf-{$datetime}";
    $this->pdfFolder  = "";
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

  public function getType()
  {
    return $this->type;
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

  public function getWarehouse()
  {
    return $this->warehouse;
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

  public function setType($type)
  {
    $this->type = $type;
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

  public function setWarehouse($warehouse)
  {
    $this->warehouse = $warehouse;
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
    // Obtener el footer
    ob_start();
    include __DIR__ . "/html/footer.php";
    $footer = ob_get_clean();

    $this->SetY(-9);
    $this->writeHTML($footer, true, false, true, false, '');
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
