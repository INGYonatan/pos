<?php
include 'inc/session.inc.php';

$id_compra   = cleanStr($_GET['uid']);
$data_compra = getPurchaseData($id_compra);

if (!$data_compra) :
  closeSession();
  die;
endif;
?>

<!DOCTYPE html>
<html lang="es" moznomarginboxes mozdisallowselectionprint>
<meta charset="UTF-8">

<head>
  <style type="text/css">
    * {
      font-size: 11px;
      font-family: 'Arial';
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

    .logo {
      width: 50%;
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
  <div id="ticket" class="ticket">
    <div class="text-center">
      <img class="logo" src="<?= ADM_LOGO; ?>" alt="logo" height="100" style="object-fit: contain;">
    </div>
    <p class="centrado">
      <span style="text-transform: uppercase;"><?= ADM_NAME; ?></span><br>
      <?= $data_compra['sucursal']; ?><br>
      <?= $data_compra['sucursal_direccion']; ?>
    </p>
    <table width="100%">
      <tr>
        <td>RFC: <?= TICKET_RFC; ?></td>
      </tr>

      <tr>
        <td class="text-right"><?= $data_compra['numero_serie']; ?></td>
      </tr>
    </table>
    <br>
    <table width="100%">
      <tr>
        <td width="33.333%" align="left"><strong><?= $data_compra['ticket_fecha']; ?></strong></td>
        <td width="33.333%" align="center"><strong>Folio: <?= $data_compra['folio']; ?></strong></td>
        <td width="33.333%" align="right"><strong><?= $data_compra['ticket_hora']; ?></strong></td>
      </tr>
    </table>
    <table width="100%">
      <tr>
        <td width="100%" align="left"><strong><?= $data_compra['cliente']; ?></strong></td>
      </tr>
    </table>
    <table class="descripcion-table" width="100%">
      <thead>
        <tr>
          <th class="text-left">Código</th>
          <th class="text-left">Descripción</th>
          <th class="text-right">Precio</th>
          <th class="text-center">Cant</th>
          <th class="text-right">Importe</th>
        </tr>
      </thead>
      <hr>
      <tbody>
        <?php foreach ($data_compra['productos'] as $key => $producto) :
          $unit_type = $producto['unidad'] === 'A granel' ? 'kg.' : 'pzs.';
        ?>
          <tr>
            <td class="text-left"><strong><?= $producto['codigo']; ?></strong></td>
            <td class="text-left"><strong><?= $producto['nombre_producto']; ?></strong></td>
            <td class="text-right"><strong>$<?= number_format($producto['precio_costo'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
            <td class="text-center"><strong><?= format_decimal_number($producto['cantidad'], DECIMALS_CURRENCY_TICKET); ?> <?= $unit_type; ?></strong></td>
            <td class="text-right"><strong>$<?= number_format($producto['total'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <table>
      <tr>
        <td width="100%"><strong>Total de articulos: <?= count($data_compra['productos']); ?></strong></td>
      </tr>
    </table>
    <hr>
    <table class="">

      <tr>
        <td width="50%"><strong>Subtotal</strong></td>
        <td width="50%" align="right"><strong>$<?= number_format($data_compra['subtotal'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr>

      <tr>
        <td width="50%"><strong>IEPS</strong></td>
        <td width="50%" align="right"><strong><?php echo '$' . number_format(($data_compra["ieps"] ?? 0), DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr>

      <tr>
        <td width="50%"><strong>IVA</strong></td>
        <td width="50%" align="right"><strong><?php echo '$' . number_format($data_compra["iva"], DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr>

      <tr>
        <td width="50%"><strong>Total pesos</strong></td>
        <td width="50%" align="right"><strong>$<?= number_format($data_compra['total'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr>

      <!-- <tr>
        <td width="50%"><strong>Pagó con: </strong></td>
        <td width="50%" align="right"><strong>$<?= number_format($data_compra['pago_con'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr>

      <td width="50%"><strong>Cambio: </strong></td>

      <td width="50%" align="right"><strong>$<?= number_format($data_compra['cambio'], DECIMALS_CURRENCY_TICKET); ?></strong></td>
      </tr> -->

      <tr>
        <td width="100%">
          <?= numtoletras($data_compra['total']) ?>
        </td>
      </tr>
    </table>
    <!--p class="centrado">Le atendió: <?= $data_compra['usuario']; ?></p-->
    <!-- <p class="centrado">Gracias por su compra</p>
    <br class="bordertop"> -->
    <p class="centrado"><strong>*GRACIAS POR SU COMPRA*</strong><br>SI DESEA FACTURAR ESTE TICKET, RECUERDE QUE DEBE SOLICITARLO DENTRO DEL MES DE COMPRA<br><br>EL IMPORTE DE ESTA COMPRA SE INCLUYE EN LA FACTURA GLOBAL DEL DÍA<br><br>Comprobante simplificado de operación con <?= $data_compra['cliente']; ?> de acuerdo al Art. 51 del reglamento del Código Fiscal de la Federación</p>

    <?php if ($data_compra['status'] == 'cancelado') : ?>
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