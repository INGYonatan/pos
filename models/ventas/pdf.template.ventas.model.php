<?php
require_once __DIR__ . "/pdf.template.ventas.listitem.model.php";
require_once __DIR__ . "/../../libs/tcpdf-sdk/vendor/autoload.php";

class PDFTemplateVentasModel extends TCPDF
{
  private $leftLogo;
  private $centerLogo;
  private $typeProducts;
  private $typeSuppliers;
  private $line;
  private $date;
  private $user;
  private $sales;
  private $pdfTitle;
  private $pdfFolder;

  public function __construct()
  {
    parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $this->leftLogo      = BASE_URL . "/src/assets/images/pdf-template-icon.png";
    $this->centerLogo    = BASE_URL . "/src/assets/images/pdf-template-logo-name.png";
    $this->typeProducts  = "Todos";
    $this->typeSuppliers = "Todos";
    $this->line          = "";
    $this->date          = date("Y-m-d H:i:s");
    $this->user          = "";
    $this->sales        = [];

    $datetime         = date("YmdHis");
    $this->pdfTitle    = "pdf-ventas-{$datetime}";
    $this->pdfFolder   = "";
  }

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

  public function getSales()
  {
    return $this->sales;
  }

  public function getPdfTitle()
  {
    return $this->pdfTitle;
  }

  public function getPdfFolder()
  {
    return $this->pdfFolder;
  }

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

  public function setSales($sales)
  {
    $this->sales = $sales;
  }

  public function setPdfTitle($pdfTitle)
  {
    $this->pdfTitle = $pdfTitle;
  }

  public function setPdfFolder($pdfFolder)
  {
    $this->pdfFolder = $pdfFolder;
  }

  public function Header()
  {
    ob_start();
    include __DIR__ . "/html/header.php";
    $header = ob_get_clean();

    $this->setY(5);
    $this->writeHTML($header, true, false, true, false, '');
  }

  public function Footer()
  {
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
}
