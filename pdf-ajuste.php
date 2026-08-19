<?php
require_once __DIR__ . "/inc/session.inc.php";
require_once __DIR__ . "/models/ajuste/pdf.template.ajuste.model.php";

$id_inventario_ajuste    = cleanStr($_GET['uid']);
$data_inventario_ajuste  = getInventoryAdjustmentData($id_inventario_ajuste);

if (!$data_inventario_ajuste) :
  closeSession();
  die;
endif;

$fileName = "ajuste-inventario-{$data_inventario_ajuste['folio']}";

$pdf = new PDFTemplateAjusteModel();

$pdf->setPdfTitle($fileName);
$pdf->setType("Ajustes");
$pdf->setConcept($data_inventario_ajuste['tipo']);
$pdf->setEntryNote("--");
$pdf->setDate($data_inventario_ajuste['ticket_fecha']);
$pdf->setWarehouse($data_inventario_ajuste['nombre_sucursal']);

$products = [];

foreach ($data_inventario_ajuste['productos'] as $producto) {
  // Calcular el precio
  $price          = $producto["precio_costo_original"];
  $unidad_entrada = $producto["unidad_entrada"];
  $unidad_salida  = $producto["unidad_salida"];
  $numero_piezas  = $producto["numero_piezas"];

  if ($unidad_entrada == "caja" && $unidad_salida == "unidad") {
    $price = $price / $numero_piezas;
  }

  $item = new PDFTemplateDefaultListItemModel();
  $item->setId($producto['codigo']);
  $item->setName($producto['nombre_producto']);
  $item->setQuantity($producto['cantidad']);
  $item->setPrice($price);

  $products[] = $item;
}

$pdf->setProducts($products);

$pdf->createPDF();
$pdf->showPDF();
