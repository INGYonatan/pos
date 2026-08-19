<?php
require_once __DIR__ .  '/inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";

$paymentId = cleanStr($_GET["uid"]);

if (!$paymentId) die("Pago no proporcionado.");

$paymentData = getSalePaymentByMd5Id($paymentId);

if (!$paymentData) die("Pago no encontrado.");

// Obtener el total de la venta
$saleTotalAmount = getSaleTotalById($paymentData["id_venta"]);
?>

<html lang="es">

<head>
  <style type="text/css">
    * {
      font-size: 13px;
      font-family: 'Arial';
    }

    td,
    th,
    tr,
    table {
      border-collapse: collapse;
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
      width: 260px;
      max-width: 260px;
    }

    img {
      max-width: inherit;
      width: inherit;
    }

    @media print {

      .oculto-impresion,
      .oculto-impresion * {
        display: none !important;
      }
    }
  </style>


  <meta charset="UTF-8">


</head>

<body>
  <div class="ticket">
    <img class="logo" src="<?= ADM_LOGO; ?>" alt="logo" height="100" style="object-fit: contain;">

    <p class="centrado">TICKET CRÉDITO
      <br><?php echo $dataVenta['FormaPago']; ?>
      <br><?php echo $dataVenta['Direccion']; ?>
      <br><?php echo $dataVenta['Telefono']; ?>
    </p>
    <table width="100%">
      <tbody>
        <tr>
          <td width="33.333%" align="left"><strong><?php echo date('d/m/Y'); ?></strong></td>
          <td width="33.333%" align="center"><strong>Folio: <?php echo $dataVenta['NumVenta']; ?></strong></td>
          <td width="33.333%" align="right"><strong><?php echo date('h:i A'); ?></strong></td>
        </tr>
      </tbody>
    </table>
    <hr>
    <table width="100%">
      <!--thead>
        <tr>
          <th class="cantidad">CANT</th>
          <th class="producto">PRODUCTO</th>
          <th class="precio">$</th>
        </tr>
      </thead-->

      <tbody class="">
        <tr>
          <td class="" width="10%"><strong>1</strong></td>
          <td class="" width="55%"><strong>Abono</strong></td>
          <td class="" align="right" width="40%"><strong><?php echo '$' . number_format($dataVenta['Abono']); ?></strong></td>
        </tr>

      </tbody>
    </table>
    <hr>
    <table class="">
      <tbody>
        <tr>
          <td width="50%"><strong>Total de la Cuenta</strong></td>
          <td width="50%" align="right"><strong><?php echo '$' . number_format($dataVenta['TotalCuenta']); ?></strong></td>
        </tr>
        <!--tr>
            <td width="50%"><strong>Saldo anterior</strong></td>
            <td width="50%" align="right"><strong><?php echo '$' . number_format($saldoAnterior); ?></strong></td>
          </tr-->
        <tr>
          <td width="50%"><strong>Saldo anterior</strong></td>
          <td width="50%" align="right"><strong><?php echo '$' . number_format($dataVenta['SaldoVenta'] + $dataVenta['Abono']); ?></strong></td>
        </tr>
        <tr>
          <td width="50%"><strong>Saldo actual</strong></td>
          <td width="50%" align="right"><strong><?php echo '$' . number_format($dataVenta['SaldoVenta']); ?></strong></td>
        </tr>


        <!--tr>
          <td width="50%"><strong>Efectivo</strong></td>
          <td width="50%" align="right"><strong>$1.00</strong></td>
        </tr-->

        <!--tr>
          <td width="50%"><strong>Cambio</strong></td>
          <td width="50%" align="right"><strong>$0.00</strong></td>
        </tr-->

        <tr>
          <td width="100%">
            <?php echo /* numtoletras($dataVenta['Abono']) */ 'abono'; ?> </td>
        </tr>
      </tbody>
    </table>
    <p class="centrado">Cajero: <?php echo $dataVenta["NombreCompleto"]; ?></p>
    <br class="bordertop">
  </div>
  <br>
  <button class="oculto-impresion" onclick="window.print()">Imprimir</button>


  <script type="text/javascript">
    /*window.onload = function() {
      window.print();
      window.close();
};*/
  </script>
</body>

</html>