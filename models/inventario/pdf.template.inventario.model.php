<?php
require_once __DIR__ . "/pdf.template.inventario.listitem.model.php";
require_once __DIR__ . "/../../libs/tcpdf-sdk/vendor/autoload.php";

class PDFTemplateInventarioModel extends TCPDF
{
  private $leftLogo;
  private $centerLogo;
  private $typeProducts;
  private $typeSuppliers;
  private $line;
  private $date;
  private $user;

  private $products;

  private $pdf;
  private $pdfTitle;
  private $pdfFolder;

  public function __construct()
  {
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->leftLogo       = ADM_FAVICON;
    $this->centerLogo     = ADM_LOGO_WIDTH_LARGE;
    $this->typeProducts   = "Todos";
    $this->typeSuppliers  = "Todos";
    $this->line           = "";
    $this->date           = date("Y-m-d H:i:s");
    $this->user           = "";

    $this->products       = [];

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

  public function getTypeProducts()
  {
    return $this->typeProducts;
  }

  public function getTypeSuppliers()
  {
    return $this->typeSuppliers;
  }

  public function getLine()
  {
    return $this->line;
  }

  public function getDate()
  {
    return $this->date;
  }

  public function getUser()
  {
    return $this->user;
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

  public function setTypeProducts($typeProducts)
  {
    $this->typeProducts = $typeProducts;
  }

  public function setTypeSuppliers($typeSuppliers)
  {
    $this->typeSuppliers = $typeSuppliers;
  }

  public function setLine($line)
  {
    $this->line = $line;
  }

  public function setDate($date)
  {
    $this->date = $date;
  }

  public function setUser($user)
  {
    $this->user = $user;
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
    /* $this->setY(-20);
    $this->Image(BASE_URL . "/src/assets/images/pdf-footer.jpeg", 0, $this->getY(), 210, 18, '', '', '', false, 300, '', false, false, 0, false, false, false); */

    // Obtener el footer
    ob_start();
    include __DIR__ . "/html/footer.php";
    $footer = ob_get_clean();

    $this->SetY(-9);
    $this->writeHTML($footer, true, false, true, false, '');
  }

  public function createPDF()
  {
    $this->SetMargins(10, 75, 10);
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
