<?php
if (!$ticket_venta_id_venta) {
  require_once __DIR__ .  '/inc/session.inc.php';
}

require_once __DIR__ .  '/data/lib/helpers/sales.helper.php';

$id_venta = $ticket_venta_id_venta ? $ticket_venta_id_venta : cleanStr($_GET['uid']);
$sale     = get_sale_data($id_venta);

if (!$sale) :
  closeSession();
  die;
endif;

$sale_ieps = $sale->sale_ieps ?? 0;

if ($sale->payment_form == "credito") {
  $query      = "SELECT SUM(monto_total) AS monto_total FROM {$db_dti}_venta_pagos WHERE id_venta = {$sale->id}";
  $result     = mysqli_query($mysqli, $query);
  $data       = mysqli_fetch_assoc($result);
  $totalPaid  = $data['monto_total'] ?? 0;
}
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

    .descripcion-table td,
    .descripcion-table th {
      padding: 4px;
      align-content: start;
      font-size: 9px;
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

    .forma_pago {
      width: 100%;
      font-size: 10px;
      font-weight: bold;
      text-align: center;
    }

    .productos-row td {
      font-weight: bold;
    }

    .checkbox {
      accent-color: black;
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
      <?= $sale->branch->name; ?><br>
      <?= $sale->branch->address; ?>
    </p>
    <!-- <table width="100%">
      <tr>
        <td>RFC: <?= TICKET_RFC; ?></td>
      </tr>

      <tr>
        <td class="text-right"><?= $sale->branch->serial_number; ?></td>
      </tr>
    </table> -->
    <br>
    <table width="100%">
      <tr>
        <td width="33.333%" align="left"><strong><?= $sale->date_format; ?></strong></td>
        <td width="33.333%" align="center"><strong>Folio: <?= $sale->folio; ?></strong></td>
        <td width="33.333%" align="right"><strong><?= $sale->time_format; ?></strong></td>
      </tr>
    </table>
    <table width="100%">
      <tr>
        <td width="100%" align="left"><strong><?= $sale->customer->name; ?></strong></td>
      </tr>
    </table>
    <?php if ($sale->status == 'cancelado') : ?>
      <table class="descripcion-table" width="100%" spacing="1">
        <thead>
          <tr>
            <th class="text-left">Cod</th>
            <th class="text-left">Desc</th>
            <th class="text-left">Cant</th>
            <th class="text-right">Precio</th>
            <th class="text-right">Importe (Sin Impuesto)</th>
          </tr>
        </thead>
        <hr>
        <tbody>
          <?php foreach ($sale->list as $key => $product) : ?>
            <tr class="productos-row">
              <td class="text-left"><?= $product->code; ?></td>
              <td class="text-left"><?= $product->name; ?></td>
              <td class="text-left"><?= $product->cart_quantity; ?> <?= $product->unit_symbol; ?></td>
              <td class="text-right">$<?= number_format($product->cart_sale_net_price, DECIMALS_CURRENCY_TICKET); ?></td>
              <td class="text-right">$<?= number_format($product->cart_sale_amount_without_iva, DECIMALS_CURRENCY_TICKET); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <table>
        <tr>
          <td width="100%"><strong>Total de articulos: <?= count($sale->list); ?></strong></td>
        </tr>
      </table>
      <hr>
      <table class="">
        <tr>
          <td width="50%"><strong>Subtotal</strong></td>
          <td width="50%" align="right"><strong>$<?= number_format($sale->sale_subtotal, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <tr>
          <td width="50%"><strong>IEPS</strong></td>
          <td width="50%" align="right"><strong>$<?= number_format($sale_ieps, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <tr>
          <td width="50%"><strong>IVA</strong></td>
          <td width="50%" align="right"><strong>$<?= number_format($sale->sale_iva, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <tr>
          <td width="50%"><strong>Total pesos</strong></td>
          <td width="50%" align="right"><strong>$<?= number_format($sale->sale_total, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <tr>
          <td width="50%"><strong>Pagó con: </strong></td>
          <td width="50%" align="right"><strong>$<?= number_format($sale->payWith, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <td width="50%"><strong>Cambio: </strong></td>

        <td width="50%" align="right"><strong>$<?= number_format($sale->exchange, DECIMALS_CURRENCY_TICKET); ?></strong></td>
        </tr>

        <tr>
          <td width="100%">
            <?= numtoletras($sale->sale_total) ?>
          </td>
        </tr>
      </table>
    <?php endif; ?>

    <?php if ($sale->status != 'cancelado') :
      $subtotal       = 0;
      $ieps           = 0;
      $iva            = 0;
      $total          = 0;
      $total_products = 0;
    ?>
      <table class="descripcion-table" width="100%" spacing="1">
        <thead>
          <tr>
            <th class="text-left">Cod</th>
            <th class="text-left">Desc</th>
            <th class="text-left">Cant</th>
            <th class="text-right">Precio</th>
            <th class="text-right">Importe (Sin Impuesto)</th>
          </tr>
        </thead>
        <hr>
        <tbody>
          <?php foreach ($sale->list as $key => $product) : ?>
            <?php if (!$product->cancelled) :
              $quantity = $product->cart_quantity;
              $subtotal = $subtotal + $product->cart_sale_amount_without_iva;
              $ieps     = $ieps + (($product->cart_sale_ieps ?? 0) * $quantity);
              $iva      = $iva + ($product->cart_sale_iva * $quantity);
              $total_products = $total_products + $quantity;
            ?>
              <tr class="productos-row">
                <td class="text-left"><?= $product->code; ?></td>
                <td class="text-left"><?= $product->name; ?></td>
                <td class="text-left"><?= $product->cart_quantity; ?> <?= $product->unit_symbol; ?></td>
                <td class="text-right">$<?= number_format($product->cart_sale_net_price, DECIMALS_CURRENCY_TICKET); ?></td>
                <td class="text-right">$<?= number_format($product->cart_sale_amount_without_iva, DECIMALS_CURRENCY_TICKET); ?></td>
              </tr>

              <?php /* $total_products++; */ ?>
            <?php endif; ?>
          <?php endforeach;
          $total = $subtotal + $ieps + $iva + $sale->sale_rounding;
          ?>
        </tbody>
      </table>
      <table>
        <tr>
          <td width="100%"><strong>Total de articulos: <?= $total_products; ?></strong></td>
        </tr>
      </table>
      <hr>
      <?php if ($sale->payment_form == "contado") : ?>
        <table class="">
          <tr>
            <td width="50%"><strong>Subtotal</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($subtotal, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>IEPS</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($ieps, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>IVA</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($iva, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>Total pesos</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($total, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>Pagó con: </strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($sale->payWith, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <td width="50%"><strong>Cambio: </strong></td>

          <td width="50%" align="right"><strong>$<?= number_format($sale->exchange, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="100%">
              <?= numtoletras($total) ?>
            </td>
          </tr>
        </table>
      <?php endif; ?>

      <?php if ($sale->payment_form == "credito") : ?>
        <table class="">
          <tr>
            <td width="50%"><strong>Subtotal</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($subtotal, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>IEPS</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($ieps, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>IVA</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($iva, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>Total pesos</strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($total, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="50%"><strong>Abonado: </strong></td>
            <td width="50%" align="right"><strong>$<?= number_format($totalPaid, DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <td width="50%"><strong>Saldo Actual: </strong></td>

          <td width="50%" align="right"><strong>$<?= number_format(($total - $totalPaid), DECIMALS_CURRENCY_TICKET); ?></strong></td>
          </tr>

          <tr>
            <td width="100%">
              <?= numtoletras($total) ?>
            </td>
          </tr>
        </table>
      <?php endif; ?>

      <table style="margin-top: 10px; pointer-events: none;">
        <tr>
          <td>
            <div class="forma_pago">
              Efectivo
              <input class="checkbox" type="checkbox" <?= $sale->efectivo > 0 ? "checked" : ""; ?>>
            </div>
          </td>

          <td>
            <div class="forma_pago">
              Transferencia
              <input class="checkbox" type="checkbox" <?= $sale->transferencia > 0 ? "checked" : ""; ?>>
            </div>
          </td>

          <td>
            <div class="forma_pago">
              T.C
              <input class="checkbox" type="checkbox" <?= $sale->tarjeta_credito > 0 ? "checked" : ""; ?>>
            </div>
          </td>

          <th>
            <div class="forma_pago">
              T.D
              <input class="checkbox" type="checkbox" <?= $sale->tarjeta_debito > 0 ? "checked" : ""; ?>>
            </div>
          </th>

          <th>
            <div class="forma_pago">
              Cheque
              <input class="checkbox" type="checkbox" <?= $sale->cheque > 0 ? "checked" : ""; ?>>
            </div>
          </th>
        </tr>
      </table>
    <?php endif; ?>
    <!--p class="centrado">Le atendió: <?php /* $sale['usuario']; */ ?></p-->
    <br>
    <p class="centrado"><?= $sale->observations; ?></p>
    <p class="centrado"><strong>*GRACIAS POR SU COMPRA*</strong><br>FACTURACIÓN AL DÍA
      (SE MANDA EN 1 DÍA HÁBIL AL CORREO
      ELECTRONICO PROPORCIONADO) FAVOR DE
      SOLICITAR EN EL MOMENTO DE SU COMPRA, DE
      LO CONTRARIO SERÁ PARTE DE LA FACTURA
      GLOBAL DEL DÍA.<br><br>Comprobante simplificado de operación con <?= $sale->customer->name; ?> de acuerdo al Art. 51 del reglamento del Código Fiscal de la Federación</p>

    <?php if ($sale->status == 'cancelado') : ?>
      <div class="mark-water-container">
        <h1 class="mark-water">Cancelado</h1>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!$ticket_venta_id_venta) : ?>
    <br />
    <button class="oculto-impresion" onclick="window.print()">Imprimir</button>
  <?php endif; ?>
</body>

</html>

<?php if (!$ticket_venta_id_venta) : ?>
  <script type="text/javascript">
    window.onload = function() {
      window.print();
    };
  </script>
<?php endif; ?>