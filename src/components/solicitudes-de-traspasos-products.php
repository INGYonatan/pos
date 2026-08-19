<?php
global $mysqli;
$transferRequestId      = $data['transferRequestId']      ?? null;
$transferRequestStatus  = $data['transferRequestStatus']  ?? "pendiente";

if (!$transferRequestId) {
  echo "ID de solicitud de traspaso no proporcionado.";
  return;
}

$productsQuery = "SELECT
    P.codigo,
    P.nombre_producto,
    ST.id_producto,
    ST.cantidad_solicitada,
    ST.cantidad_atendida
  FROM
    paal_solicitud_transferencia_productos AS ST
  LEFT JOIN
    paal_productos AS P ON ST.id_producto = P.id_producto
  WHERE
    ST.id_solicitud_transferencia = {$transferRequestId}
";

$productsResult = mysqli_query($mysqli, $productsQuery);
?>

<div class="responsive-table">
  <table class="table table-sm table-hover table-striped">
    <thead class="bg-dark text-white">
      <tr>
        <th>Código</th>
        <th>Producto</th>
        <th class="text-center">Cantidad solicitada</th>

        <?php if ($transferRequestStatus === "completado") : ?>
          <th class="text-center">Cantidad atendida</th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php while ($product = mysqli_fetch_assoc($productsResult)) : ?>
        <tr>
          <td><?= htmlentities($product['codigo']); ?></td>
          <td><?= htmlentities($product['nombre_producto']); ?></td>
          <td class="text-center"><?= htmlentities($product['cantidad_solicitada']); ?></td>

          <?php if ($transferRequestStatus === "completado") : ?>
            <td class="text-center"><?= htmlentities($product['cantidad_atendida']); ?></td>
          <?php endif; ?>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>