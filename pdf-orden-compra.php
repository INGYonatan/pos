<?php
require_once 'inc/session.inc.php';
require_once 'data/lib/tcpdf/vendor/autoload.php';
require_once 'data/pdf/cotizacion/pdf.php';
require_once 'data/lib/helpers/purchase-orders.helper.php';

$purchaseOrderId = cleanStr($_GET['uid']);

if (empty($purchaseOrderId)) :
  closeSession();
  die;
endif;
/* 
  $purchaseOrder->id                   = $data['id_orden_compra'];
  $purchaseOrder->user_id              = $data['id_usuario'];
  $purchaseOrder->branch_id            = $data['id_sucursal'];
  $purchaseOrder->supplier_id          = $data['id_proveedor'];
  $purchaseOrder->folio                = $data['folio'];
  $purchaseOrder->document_folio       = $data['folio_documento'];
  $purchaseOrder->document_date        = $data['fecha_documento'];
  $purchaseOrder->document_date_format = $data['fecha_documento_formato'];
  $purchaseOrder->payment_method       = $data['metodo_pago'];
  $purchaseOrder->payment_form         = $data['forma_pago'];
  $purchaseOrder->observations         = $data['observaciones'];
  $purchaseOrder->type                 = $data['tipo'];
  $purchaseOrder->total                = $data['total'];
  $purchaseOrder->list                 = purchase_order_get_products($purchase->id, $purchase->branch_id);
*/

$purchaseOrder = purchase_order_get_by_id($purchaseOrderId);
$branchData             = getBranchOfficeData($purchaseOrder->branch_id);

if (!$purchaseOrder) :
  closeSession();
  die;
endif;

$company                = new stdClass();
$company->logo          = PDF_LOGO;
$company->name          = ADM_NAME;
$company->branch        = $branchData["nombre_sucursal"];
$company->address       = $branchData["direccion"];
$company->phone         = $branchData["telefono"];
$company->whatsapp      = $branchData["whatsapp"];
$company->email         = $branchData["correo"];
$company->socialReason  = $branchData["razon_social"];
$company->rfc           = $branchData["rfc"];

$seller                 = new stdClass();
$seller->number         = $purchaseOrder->seller->id;
$seller->name           = $purchaseOrder->seller->name;
$seller->email          = $purchaseOrder->seller->email;

$products               = new stdClass();
$products->list         = $purchaseOrder->list;
$products->shipment     = 0;
$products->subtotal     = $purchaseOrder->subtotal;
$products->ieps         = $purchaseOrder->ieps ?? 0;
$products->iva          = $purchaseOrder->iva;
$products->total        = $purchaseOrder->total;

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(ADM_NAME);
$pdf->SetTitle('ORDEN COMRPA' . $purchaseOrder->folio);

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('helvetica', '', 12);

ob_start();
require_once __DIR__ . "/data/pdf/templates/purchase-order/page01.php";
$page01 = ob_get_clean();

$pdf->writeHTML($page01, true, false, true, false, '');

$pdf->Output('ORDEN_COMPRA-' . $purchaseOrder->folio . '.pdf', 'I');
