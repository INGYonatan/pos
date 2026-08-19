<?php
require_once __DIR__ . "/inc/session.inc.php";
require_once __DIR__ . "/models/traspaso/pdf.template.traspaso.model.php";

$id_transferencia   = cleanStr($_GET['uid']);
$data_transferencia = getInventoryTransferData($id_transferencia);

if (!$data_transferencia) :
  closeSession();
  die;
endif;

$fileName = "traspaso-{$data_transferencia['folio']}";

$pdf = new PDFTemplateTraspasoModel();

$pdf->setPdfTitle($fileName);
/* $pdf->setConcept($data_inventario_ajuste['tipo']); */
/* $pdf->setEntryNote($data_inventario_ajuste['folio']); */
$pdf->setOriginWarehouse($data_transferencia['sucursal_origen']);
$pdf->setDestinationWarehouse($data_transferencia['sucursal_destino']);
$pdf->setDate($data_transferencia['ticket_fecha']);
$pdf->setFolio($data_transferencia['folio']);

$products = [];

foreach ($data_transferencia['productos'] as $producto) {
  $name = $producto['nombre_producto'];

  if ($producto["serial_numbers"]) $name .= "<br><small><strong>Números de serie:</strong> " . implode(", ", $producto["serial_numbers"]) . "</small>";

  $item = new PDFTemplateDefaultListItemModel();
  $item->setId($producto['codigo']);
  $item->setName($name);
  $item->setQuantity($producto['cantidad']);
  $item->setPrice($producto['precio_venta']);

  $products[] = $item;
}

$pdf->setProducts($products);

$pdf->createPDF();
$pdf->showPDF();
