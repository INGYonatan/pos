<?php
require_once __DIR__ . "/../inc/session.inc.php";
require_once __DIR__ . "/../models/ventas/pdf.template.ventas.model.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$month      = cleanStr($_GET["month"]);
$branchId   = cleanStr($_GET["sid"]);
$brandId    = cleanStr($_GET["brandId"]);
$categoryId = cleanStr($_GET["categoryId"]);
$familyId   = cleanStr($_GET["familyId"]);
$typeIds    = cleanStr($_GET["typeIds"]);

if (!$IS_ADMIN) $branchId = getSessionBranchOfficeId();

$byMonth      = $month      ? "DATE_FORMAT(V.fecha_creacion, '%Y-%m') = '{$month}'" : "1=1";
$byBranchId   = $branchId   ? "V.id_sucursal = {$branchId}" : "1=1";
$byBrandId    = $brandId    ? "P.id_marca = {$brandId}" : "1=1";
$byCategoryId = $categoryId ? "P.id_categoria = {$categoryId}" : "1=1";
$byFamilyId   = $familyId   ? "P.id_categoria_familia = {$familyId}" : "1=1";
$byTypeIds    = $typeIds    ? "P.id_tipo IN ({$typeIds})" : "1=1";

$orders = [];
$orders[] = "V.id_sucursal ASC";
$orders[] = "M.marca ASC";
$orders[] = "P.id_categoria ASC";
$orders[] = "P.id_categoria_familia ASC";
$orders = implode(", ", $orders);

$query = "SELECT
    VP.id_producto,
    VP.nombre_producto,
    VP.precio AS precio_venta,
    VP.cantidad,
    V.id_sucursal,
    P.codigo,
    P.precio_costo_original AS precio_costo,
    P.unidad_entrada,
    P.unidad_salida,
    P.numero_piezas,
    C.categoria AS linea,
    M.marca AS marca,
    S.nombre_sucursal AS sucursal,
    DATE_FORMAT(V.fecha_creacion, '%Y-%m-%d') AS fecha_venta
  FROM
    {$db_dti}_venta_productos AS VP
  LEFT JOIN
    {$db_dti}_ventas AS V ON (VP.id_venta = V.id_venta)
  LEFT JOIN
    {$db_dti}_productos AS P ON (VP.id_producto = P.id_producto)
  LEFT JOIN
    {$db_dti}_categorias AS C ON (P.id_categoria = C.id_categoria)
  LEFT JOIN
    {$db_dti}_marcas AS M ON (P.id_marca = M.id_marca)
  LEFT JOIN
    {$db_dti}_sucursales AS S ON (V.id_sucursal = S.id_sucursal)
  WHERE
    V.status != 'cancelado' AND
    VP.cancelado = 'no' AND
    {$byMonth} AND
    {$byBranchId} AND
    {$byBrandId} AND
    {$byCategoryId} AND
    {$byFamilyId} AND
    {$byTypeIds}
  ORDER BY
    {$orders}
";

$queryResult = mysqli_query($mysqli, $query);

$products = [];

while ($row = mysqli_fetch_array($queryResult)) {
  $productId = $row['id_producto'];
  $id = "{$productId}-{$row['id_sucursal']}";

  $productUnitEntry     = $row["unidad_entrada"];
  $productPiecesPerUnit = intval($row["numero_piezas"]);

  $quantity   = intval($row['cantidad']);
  $costPrice  = floatval($row['precio_costo']);
  $salePrice  = floatval($row['precio_venta']);

  if ($productUnitEntry == "caja") $costPrice = $costPrice / $productPiecesPerUnit;

  $amountCost = $costPrice * $quantity;
  $amountSale = $salePrice * $quantity;

  if (isset($products[$id])) {
    $products[$id]["total_cantidad"]     += $quantity;
    $products[$id]["total_precio_costo"] += $amountCost;
    $products[$id]["total_precio_venta"] += $amountSale;
    continue;
  }

  $row["total_cantidad"]     = $quantity;
  $row["total_precio_costo"] = $amountCost;
  $row["total_precio_venta"] = $amountSale;

  $products[$id] = $row;
}

$sales = [];
foreach ($products as $product) {
  $item = new PDFTemplateVentasListItemModel();
  $item->setDate($product['fecha_venta']);
  $item->setBrand($product['marca']);
  $item->setLine($product['linea']);
  $item->setCode($product['codigo']);
  $item->setName($product['nombre_producto']);
  $item->setQuantity($product['total_cantidad']);
  $item->setTotalCost($product['total_precio_costo']);
  $item->setTotalSale($product['total_precio_venta']);
  $item->setBranch($product['sucursal']);

  $sales[] = $item;
}

$branchLabel = "Todas";
if ($branchId) {
  $branchOfficeData = getBranchOfficeData($branchId);
  if (!empty($branchOfficeData["nombre_sucursal"])) {
    $branchLabel = $branchOfficeData["nombre_sucursal"];
  }
}

$fileName = "ventas-" . date("YmdHis");

$pdf = new PDFTemplateVentasModel();
$pdf->setPdfTitle($fileName);
$pdf->setTypeProducts($branchLabel);
$pdf->setTypeSuppliers($branchLabel);
$pdf->setLine($month ?: "Todos");
$pdf->setDate(date("d/m/Y h:i a"));
$pdf->setUser($admp_session_user_data["nombre_completo"]);
$pdf->setSales($sales);

$pdf->createPDF();
$pdf->showPDF();
