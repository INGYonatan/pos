<?php
include 'inc/session.inc.php';

$id_transferencia   = cleanStr($_GET['uid']);

header("location:" . BASE_URL . "/pdf-traspaso.php?uid={$id_transferencia}");
die;

$data_transferencia = getInventoryTransferData($id_transferencia);

if (!$data_transferencia) :
  closeSession();
  die;
endif;
?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
  <style type="text/css">
    * {
      font-size: 11px;
      font-family: 'Arial';
    }

    h3 {
      font-size: 16px;
    }

    .logo {
      width: 50%;
    }

    td,
    th,
    tr,
    table {
      border-collapse: collapse;
    }

    .text-right {
      text-align: right;
    }

    .text-left {
      text-align: left;
    }

    .text-center {
      text-align: center;
    }

    td.producto,
    th.producto {
      width: 75px;
      max-width: 75px;
    }

    td.cantidad,
    th.cantidad {
      width: 40px;
      max-width: 40px;
      word-break: break-all;
    }

    td.precio,
    th.precio {
      width: 40px;
      max-width: 40px;
      word-break: break-all;
    }

    .centrado {
      text-align: center;
      align-content: center;
    }

    .ticket {
      width: 270px;
      max-width: 270px;
      position: relative;
    }

    img {
      max-width: inherit;
      width: inherit;
    }

    .descripcion-table td strong {
      font-size: 8.5px;
    }

    @media print {

      .oculto-impresion,
      .oculto-impresion * {
        display: none !important;
      }
    }

    .mark-water-container {
      position: absolute;
      left: 0;
      right: 0;
      top: 0;
      bottom: 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .mark-water {
      transform: rotate(-45deg);
      font-size: 50px;
      color: grey;
      opacity: 0.3;
      font-weight: bold;
    }
  </style>
</head>

<body>
  <div class="ticket">
    <!--div class="text-center">
            <img class="logo" src="logo.jpg" alt="logo">
        </div-->
    <h3 class="centrado">
      <?= $data_transferencia['sucursal_origen']; ?>
      <br>Transferencia de productos a <?= $data_transferencia['sucursal_destino']; ?>
    </h3>
    <table width="100%">
      <tr>
        <td width="33.333%" align="left"><strong><?= $data_transferencia['ticket_fecha']; ?></strong></td>
        <td width="33.333%" align="center"><strong>Folio: <?= $data_transferencia['folio']; ?></strong></td>
        <td width="33.333%" align="right"><strong><?= $data_transferencia['ticket_hora']; ?></strong></td>
      </tr>
    </table>
    <table width="100%">
      <thead>
        <tr>
          <th class="text-left" style="width: 20%;">Artículo</th>
          <th class="text-left" style="width: 50%;">Descripción</th>
          <th class="text-left" style="width: 20%;">Precio</th>
          <th class="text-right" style="width: 10%;">Ajuste</th>
        </tr>
      </thead>
      <hr>
      <tbody class="descripcion-table">
        <?php $total = 0; ?>
        <?php foreach ($data_transferencia['productos'] as $key => $producto) :
          $unit_type = $producto['unidad'] === 'A granel' ? 'kg.' : '';
          $total = $total + ($producto['precio_venta'] * $producto['cantidad']);
        ?>
          <tr>
            <td class="text-left"><strong><?= $producto['codigo']; ?></strong></td>
            <td class="text-left"><strong><?= $producto['nombre_producto']; ?></strong></td>
            <td class="text-left"><strong>$<?= number_format($producto['precio_venta'], DECIMALS_CURRENCY); ?></strong></td>
            <td class="text-right"><strong><?= format_decimal_number($producto['cantidad']); ?> <?= $unit_type; ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br>
    <table>
      <tr>
        <td width="100%"><strong>Total de articulos: <?= count($data_transferencia['productos']); ?></strong></td>
      </tr>
      <tr></tr>
      <tr>
        <td width="100%"><strong>Total precio: $<?= number_format($total, DECIMALS_CURRENCY); ?></strong></td>
      </tr>
    </table>
    <hr>
    <table>
      <tr>
        <td>Realizó: </td>
        <td><?= $data_transferencia['nombre_completo']; ?></td>
      </tr>

      <tr>
        <td>Recibió: </td>
        <td><?= $data_transferencia['sucursal_destino']; ?></td>
      </tr>
    </table>

    <?php if ($data_transferencia['status'] == 'cancelado') : ?>
      <div class="mark-water-container">
        <h1 class="mark-water">Cancelado</h1>
      </div>
    <?php endif; ?>
  </div>
  <br />
  <button class="oculto-impresion" onclick="window.print()">Imprimir</button>
</body>

</html>
<script type="text/javascript">
  window.onload = function() {
    window.print();
  };
</script>