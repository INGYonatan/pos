<?php
include 'inc/session.inc.php';

$id_corte   = cleanStr($_GET['uid']);
$data_corte = getCashRegisterData($id_corte);

if (!$data_corte) :
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
      <?= $data_corte['nombre_sucursal']; ?>
      <br>Corte de ventas por articulo
    </h3>
    <table width="100%">
      <tr>
        <td width="33.333%" align="left"><strong><?= $data_corte['ticket_fecha']; ?></strong></td>
        <td width="33.333%" align="center"><strong>Folio: <?= $data_corte['folio']; ?></strong></td>
        <td width="33.333%" align="right"><strong><?= $data_corte['ticket_hora']; ?></strong></td>
      </tr>
    </table>
    <table class="descripcion-table" width="100%">
      <thead>
        <tr>
          <th class="text-left">Código</th>
          <th class="text-left">Descripción</th>
          <th class="text-left">Precio</th>
          <th class="text-left">Cant</th>
          <th class="text-right">Importe</th>
        </tr>
      </thead>
      <hr>
      <tbody class="">
        <?php foreach ($data_corte['productos'] as $key => $producto) :
          $unit_type = $producto['unidad'] === 'A granel' ? 'kg.' : '';
        ?>
          <tr>
            <td class="text-left"><strong><?= $producto['codigo']; ?></strong></td>
            <td class="text-left"><strong><?= $producto['nombre_producto']; ?></strong></td>
            <td class="text-right"><strong>$<?= number_format($producto['precio_venta'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
            <td class="text-right"><strong><?= format_decimal_number($producto['cantidad'], DECIMALS_CURRENCY_TICKET); ?> <?= $unit_type; ?></strong></td>
            <td class="text-right"><strong>$<?= number_format($producto['total'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <table>
      <tr>
        <td width="100%"><strong>Total de articulos: <?= count($data_corte['productos']); ?></strong></td>
      </tr>
    </table>
    <hr>
    <table class="">
      <tr>
        <td width="100%" align="right"><strong>Gran total $<?= number_format($data_corte['total'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
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