<?php
include 'inc/session.inc.php';

$id_sucursal = cleanStr($_GET['sid']);

$redirect = BASE_URL . "/pdf-inventario";

if ($id_sucursal) $redirect .= "?sid={$id_sucursal}";

header("Location: {$redirect}");
die;


$data_inventario = getInventoryData($id_sucursal);
$nombre_sucursal = '';

if ($id_sucursal) :
  $data_sucursal    = getBranchOfficeData($id_sucursal);
  $nombre_sucursal  = $data_sucursal['nombre_sucursal'];
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
  </style>
</head>

<body>
  <div class="ticket">
    <!--div class="text-center">
            <img class="logo" src="logo.jpg" alt="logo">
        </div-->
    <h3 class="centrado">
      <?= ADM_NAME; ?><br>
      <small>Reporte de inventario físico</small>
      <?php if (!$id_sucursal) : ?>
        <br><small>Global</small>
      <?php endif; ?>
      <?php if ($id_sucursal) : ?>
        <br><small>Suc: <?= $nombre_sucursal; ?></small>
      <?php endif; ?>
    </h3>
    <table width="100%">
      <tr>
        <td width="33.333%" align="left"><strong><?php echo date('d/m/Y'); ?></strong></td>
        <td width="33.333%" align="center"></td>
        <td width="33.333%" align="right"><strong><?php echo date('h:i A'); ?></strong></td>
      </tr>
    </table>
    <table width="100%">
      <thead>
        <tr>
          <th class="text-left">Artículo</th>
          <th class="text-left">Descripción</th>
          <th class="text-right">Precio</th>
          <th class="text-right">Cantidad real</th>
        </tr>
      </thead>
      <hr>
      <tbody class="descripcion-table">
        <?php $total = 0; ?>
        <?php foreach ($data_inventario as $key => $producto) :
          $unit_type = $producto['unidad'] === 'A granel' ? 'kg.' : '';
          $total = $total + ($producto['precio_venta'] * $producto['existencia']);
        ?>
          <tr>
            <td class="text-left"><strong><?= $producto['codigo']; ?></strong></td>
            <td class="text-left"><strong><?= $producto['nombre_producto']; ?></strong></td>
            <td class="text-right"><strong>$<?= number_format($producto['precio_venta'], DECIMALS_CURRENCY); ?></strong></td>
            <td class="text-right"><strong><?= format_decimal_number($producto['existencia']); ?> <?= $unit_type; ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <br>
    <table>
      <tr>
        <td width="100%"><strong>Total de articulos: <?= count($data_inventario); ?></strong></td>
      </tr>
      <tr></tr>
      <tr>
        <td width="100%"><strong>Total precio: $<?= number_format($total, DECIMALS_CURRENCY); ?></strong></td>
      </tr>
    </table>
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