<?php
require_once __DIR__ . "/../inc/session.inc.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$fecha_hoy        = date('Ym');
$titulo_excel     = 'Productos - ' . $fecha_hoy;
$table_row_number = (($page - 1) * $per_page) + 1;

header('Content-Type: text/html; charset=utf-8');
header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
header('Content-Disposition: attachment; filename=' . $titulo_excel . '.xls');

$month      = $_GET["month"];
$branchId   = cleanStr($_GET["sid"]);
$brandId    = cleanStr($_GET["brandId"]);
$categoryId = cleanStr($_GET["categoryId"]);
$familyId   = cleanStr($_GET["familyId"]);
$typeIds    = cleanStr($_GET["typeIds"]);

if (!$IS_ADMIN) $branchId = getSessionBranchOfficeId();
// Marca 2 (id_marca)
// Linea .
// Clave
// Nombre .
// Costo
// Precio Venta .
// Sucursal

$byMonth      = $month      ? "DATE_FORMAT(V.fecha_creacion, '%Y-%m') = '{$month}'" : "1=1";
$byBranchId   = $branchId   ? "V.id_sucursal          = {$branchId}"    : "1=1";
$byBrandId    = $brandId    ? "P.id_marca             = {$brandId}"     : "1=1";
$byCategoryId = $categoryId ? "P.id_categoria         = {$categoryId}"  : "1=1";
$byFamilyId   = $familyId   ? "P.id_categoria_familia = {$familyId}"    : "1=1";
$byTypeIds    = $typeIds    ? "P.id_tipo IN ({$typeIds})"               : "1=1";

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
    V.status    != 'cancelado'  AND
    VP.cancelado = 'no'         AND
    {$byMonth}                  AND
    {$byBranchId}               AND
    {$byBrandId}                AND
    {$byCategoryId}             AND
    {$byFamilyId}               AND
    {$byTypeIds}
  ORDER BY
    {$orders}
";

$queryResult = mysqli_query($mysqli, $query);

// Agrupar los productos por id, sumar, cantidades, precios, etc
$products = [];

while ($row = mysqli_fetch_array($queryResult)) {
  $productId  = $row['id_producto'];
  $id = "{$productId}-{$row['id_sucursal']}";

  $productUnitEntry     = $row["unidad_entrada"];
  $productPiecesPerUnit = intval($row["numero_piezas"]);

  $quantity   = intval($row['cantidad']);
  $costPrice  = floatval($row['precio_costo']);
  $salePrice  = floatval($row['precio_venta']);

  if ($productUnitEntry == "caja") $costPrice = $costPrice / $productPiecesPerUnit;

  $amountCost = $costPrice * $quantity;
  $amountSale = $salePrice * $quantity;

  if ($products[$id]) {
    $products[$id]["total_cantidad"]     += $quantity;
    $products[$id]["total_precio_costo"] += $amountCost;
    $products[$id]["total_precio_venta"] += $amountSale;

    continue;
  }

  if (!$products[$id]) {
    $row["total_cantidad"]     = $quantity;
    $row["total_precio_costo"] = $amountCost;
    $row["total_precio_venta"] = $amountSale;

    $products[$id]      = $row;
  }
}
?>

<table class="table table-hover">
  <thead class="table-dark">
    <tr>
      <th style="width: 10px;">#</th>
      <th>Fecha Venta</th>
      <th>Marca</th>
      <th><?= utf8_decode('Línea'); ?></th>
      <th>Clave</th>
      <th>Nombre</th>
      <th>Cantidad</th>
      <th>Costo total</th>
      <th>Precio total</th>
      <th>Sucursal</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($products as $row) :  ?>
      <tr>
        <th scope="row">
          <?= $table_row_number; ?>
        </th>

        <td><?= utf8_decode($row['fecha_venta']); ?></td>
        <td><?= utf8_decode($row['marca']); ?></td>
        <td><?= utf8_decode($row['linea']); ?></td>
        <td><?= utf8_decode($row['codigo']); ?></td>
        <td><?= utf8_decode($row['nombre_producto']); ?></td>
        <td><?= utf8_decode($row['total_cantidad']); ?></td>
        <td>$<?= number_format($row['total_precio_costo'], 2); ?></td>
        <td>$<?= number_format($row['total_precio_venta'], 2); ?></td>
        <td><?= utf8_decode($row['sucursal']); ?></td>
      </tr>

      <?php $table_row_number++; ?>
    <?php endforeach; ?>
  </tbody>
</table>