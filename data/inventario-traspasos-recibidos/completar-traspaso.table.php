<?php
$productsQuery = "SELECT
    ITP.id_inventario_transferencia_producto,
    ITP.id_producto,
    ITP.cantidad,
    P.codigo,
    P.nombre_producto
  FROM
    {$db_dti}_inventario_transferencia_productos AS ITP
  LEFT JOIN
    {$db_dti}_productos AS P ON ITP.id_producto = P.id_producto
  WHERE
    ITP.id_inventario_transferencia = {$inventoryTransferId} AND
    ITP.cancelado                   = 'no'
";

$productsQueryResult = mysqli_query($mysqli, $productsQuery);

$products = array_map(fn($productRow) => [
  "productId" => $productRow["id_producto"],
  "code"      => $productRow["codigo"],
  "name"      => $productRow["nombre_producto"],
  "quantity"  => $productRow["cantidad"],
], mysqli_fetch_all($productsQueryResult, MYSQLI_ASSOC));
?>

<table class="table">
  <thead>
    <tr class="table-dark">
      <th>Código</th>
      <th>Producto</th>
      <th class="text-center">Cantidad enviada</th>
      <th class="text-center">Cantidad recibida</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($products as $product) :
      $productId  = $product["productId"];
      $code       = $product["code"];
      $name       = $product["name"];
      $quantity   = floatval($product["quantity"]);
    ?>
      <tr class="align-middle">
        <td>
          <?= $code; ?>
        </td>

        <td>
          <?= $name; ?>
        </td>

        <td class="text-center">
          <?= $quantity; ?>
          <input type="hidden">
        </td>

        <td class="text-center">
          <input style="max-width: 6rem;"
            class="form-control completar-traspaso-cantidad mx-auto text-center"
            min="0"
            max="<?= $quantity; ?>"
            value="<?= $quantity; ?>"
            data-uid="<?= $productId; ?>"
            type="number"
            required>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>