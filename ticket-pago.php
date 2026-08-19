<?php
require_once __DIR__ .  '/inc/session.inc.php';

$paymentId    = cleanStr($_GET["uid"]);

if (!$paymentId) die("No se especificó el pago.");

$paymentData  = getSalePaymentByMd5Id($paymentId);

if (!$paymentData) die("El pago no existe.");

$saleData     = getSaleById($paymentData["id_venta"]);
$branchData   = getBranchOfficeData($paymentData["id_sucursal"]);
$userData     = getUserData($paymentData["id_usuario"]);
$customerData = getCustomerById($saleData["id_cliente"]);

$datetime     = parseDateTimeToSpanishParts($paymentData["fecha_hora"]);

$totalPaid    = getSaleTotalPaidById($saleData["id_venta"]);
$totalToPay   = getSaleTotalById($saleData["id_venta"]);
$balance      = $totalToPay - $totalPaid;
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Ticket de Pago a Crédito</title>
  <style>
    * {
      font-size: 10px;
      font-family: 'Arial', sans-serif;
      box-sizing: border-box;
    }

    body {
      width: 270px;
      margin: 0 auto;
      background: #fff;
      color: #222;
    }

    .ticket {
      width: 100%;
      padding: 16px 8px;
      position: relative;
    }

    .logo {
      text-align: center;
      margin-bottom: 8px;
    }

    .logo img {
      max-width: 110px;
      height: auto;
    }

    .header {
      text-align: center;
      margin-bottom: 10px;
      font-size: 10px;
    }

    .details {
      display: flex;
      justify-content: space-between;
      font-size: 10px;
      margin-bottom: 8px;
    }

    .customer {
      font-weight: bold;
      font-size: 10px;
      margin-bottom: 10px;
      margin-top: 2px;
    }

    .section-title {
      font-weight: bold;
      margin-top: 12px;
      margin-bottom: 6px;
      font-size: 10.5px;
    }

    table.payment-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9px;
      margin-bottom: 10px;
    }

    table.payment-table th,
    table.payment-table td {
      border-bottom: 1px solid #eee;
      text-align: left;
      padding: 4px 2px;
      font-size: 9px;
    }

    table.payment-table th {
      font-weight: bold;
    }

    .resumen {
      font-size: 10px;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .resumen .row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
    }

    .resumen .total {
      font-weight: bold;
      font-size: 10.5px;
      color: #007b40;
    }

    .saldo-letras {
      text-align: center;
      font-size: 10px;
      font-weight: bold;
      margin: 12px 0 8px 0;
      letter-spacing: 1px;
    }

    .nota {
      margin: 14px 0 8px 0;
      font-size: 10px;
      text-align: center;
      font-weight: bold;
    }

    .footer {
      font-size: 9px;
      text-align: center;
      margin-top: 12px;
      color: #222;
    }

    .legal {
      font-size: 8px;
      text-align: center;
      margin-top: 8px;
      color: #666;
    }

    @media print {
      body {
        margin: 0;
      }

      .ticket {
        padding: 0;
      }

      .imprimir-btn {
        display: none !important;
      }
    }
  </style>
</head>

<body>
  <div class="ticket">
    <div class="logo">
      <img class="logo" src="<?= ADM_LOGO; ?>" alt="logo" height="100" style="object-fit: contain; margin: 0 auto;">
      <br><br><br>
      <span style="font-family: Arial, sans-serif; font-size: 1.1em; font-weight:bold;"><?= ADM_NAME; ?></span>
    </div>

    <div class="header">
      <?= $branchData["nombre_sucursal"] ?><br>
      <span style="text-transform: uppercase;"><?= $branchData["direccion"]; ?></span>
    </div>

    <div class="details">
      <span><?= $datetime["date"]; ?></span>
      <span>Folio: <?= $paymentData["folio"]; ?></span>
      <span><?= $datetime["time"]; ?></span>
    </div>

    <div class="customer">
      <?= $customerData["nombre_completo"]; ?>
    </div>

    <div class="section-title">Pago a crédito</div>
    <table class="payment-table">
      <thead>
        <tr>
          <th>Desc</th>
          <th style="text-align: end;">Monto</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Abono Venta <?= $saleData["folio"]; ?></td>
          <td style="text-align: end;">$150.00</td>
        </tr>
      </tbody>
    </table>
    <div class="resumen">
      <div class="row"><span>Total de venta</span><span>$<?= number_format($totalToPay, DECIMALS_CURRENCY_TICKET); ?></span></div>
      <div class="row"><span>Total abonado</span><span>$<?= number_format($totalPaid, DECIMALS_CURRENCY_TICKET); ?></span></div>
      <div class="row total"><span>Saldo pendiente</span><span>$<?= number_format($balance, DECIMALS_CURRENCY_TICKET); ?></span></div>
      <div class="row"><span>Notas:</span><span><?= $paymentData["notas"] ? $paymentData["notas"] : "Sin observaciones"; ?></span></div>
    </div>
    <div class="saldo-letras">
      <!-- DOSCIENTOS CINCUENTA PESOS 00/100 M.N. -->
      <?= numtoletras($balance); ?>
    </div>
    <div class="nota">
      *GRACIAS POR SU PAGO*
    </div>
    <?php /* 
    <div class="footer">
      SI DESEA FACTURAR ESTE TICKET, SOLICÍTELO DENTRO DEL MES DE COMPRA<br>
      EL IMPORTE DE ESTA VENTA SE INCLUYE EN LA FACTURA GLOBAL DEL DÍA
    </div>
    */ ?>
    <div class="legal">
      Comprobante simplificado de abono realizado.<br>
      Para cualquier duda, consulte en sucursal.
    </div>
  </div>
  <button class="imprimir-btn" onclick="window.print()">Imprimir</button>
  <script>
    window.onload = function() {
      window.print();
    };
  </script>
</body>

</html>