<?php
require_once 'inc/session.inc.php';
require_once 'data/lib/tcpdf/vendor/autoload.php';
require_once 'data/pdf/cotizacion/pdf.php';
require_once 'data/lib/helpers/compras.helper.php';

$compraId = cleanStr($_GET['uid']);

if (empty($compraId)) :
  closeSession();
  die;
endif;

$compra = compra_get_by_id($compraId);
$branchData = getBranchOfficeData($compra->branch_id);

if (!$compra) :
  closeSession();
  die;
endif;

$company                = new stdClass();
$company->logo          = ADM_FAVICON;
$company->name          = ADM_NAME;
$company->branch        = $branchData["nombre_sucursal"];
$company->address       = $branchData["direccion"];
$company->phone         = formatPhoneNumber($branchData["telefono"]);
$company->whatsapp      = formatPhoneNumber($branchData["telefono"]);
$company->email         = $branchData["correo"];
$company->socialReason  = 'GRUPO FINANCIERO PAAL S.A. DE C.V.';
$company->rfc           = 'GFP140325SX0';
//$company->rfc           = $branchData["rfc"];

$seller                 = new stdClass();
$seller->number         = $compra->seller->id;
$seller->name           = $compra->seller->name;
$seller->email          = $compra->seller->email;

$products               = new stdClass();
$products->list         = $compra->list;
$products->shipment     = 0;
$products->subtotal     = $compra->subtotal;
$products->ieps         = $compra->ieps ?? 0;
$products->iva          = $compra->iva;
$products->total        = $compra->total;

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(ADM_NAME);
$pdf->SetTitle('COMRPA' . $compra->folio);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

ob_start();
require_once __DIR__ . "/data/pdf/templates/compra/page01.php";
$page01 = ob_get_clean();

$pdf->writeHTML($page01, true, false, true, false, '');

$pdf->Output('COMPRA-' . $compra->folio . '.pdf', 'I');
