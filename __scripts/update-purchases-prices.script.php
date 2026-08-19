<?php
require_once __DIR__ . "/../inc/session.inc.php";

$query = "SELECT
    CP.*,
    P.unidad_entrada,
    P.numero_piezas
  FROM
    paal_compra_productos AS CP
  INNER JOIN
    paal_productos AS P ON (CP.id_producto = P.id_producto)
  WHERE
    P.unidad_entrada = 'caja'
";

$result   = mysqli_query($mysqli, $query);
$numRows  = mysqli_num_rows($result);

if ($numRows == 0) {
  die("No products found with 'caja' as unit of entry.");
}

while ($row = mysqli_fetch_assoc($result)) {
  echo "Processing Product ID: " . $row['id_producto'] . " {$row["nombre_producto"]}<br>";
  $piecesNumber   = (int)$row["numero_piezas"];
  $costPrice      = (float)$row["precio_costo"];
  $originalPrice  = (float)$row["precio_original"];
  $subtotal       = (float)$row["subtotal"];
  $iva            = (float)$row["iva"];
  $total          = (float)$row["total"];

  $newCostPrice      = round($costPrice / $piecesNumber, DECIMALS_CURRENCY);
  $newOriginalPrice  = round($originalPrice / $piecesNumber, DECIMALS_CURRENCY);
  $newSubtotal       = round($subtotal / $piecesNumber, DECIMALS_CURRENCY);
  $newIva            = round($iva / $piecesNumber, DECIMALS_CURRENCY);
  $newTotal          = round($total / $piecesNumber, DECIMALS_CURRENCY);

  $updateQuery = "UPDATE paal_compra_productos
    SET
      precio_costo    = {$newCostPrice},
      precio_original = {$newOriginalPrice},
      subtotal        = {$newSubtotal},
      iva             = {$newIva},
      total           = {$newTotal}
    WHERE
      id_compra_producto = {$row['id_compra_producto']}
  ";

  $updateResult = mysqli_query($mysqli, $updateQuery);

  if ($updateResult) {
    echo "Updated successfully.<br><br>";
  }

  if (!$updateResult) {
    echo "Error updating record: " . mysqli_error($mysqli) . "<br><br>";
  }
}
