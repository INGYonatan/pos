<?php
$table_row_number = (($page - 1) * $per_page) + 1;
$totalAmount      = 0;

/**
 * @var bool $hasPPDInvoice
 */
?>

<div class="table-responsive min">
  <table class="table mt-4 table-centered table-xs table-hover">
    <thead>
      <tr>
        <th>#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Notas</th>
        <th>Método de pago</th>

        <?php if ($hasPPDInvoice) : ?>
          <th class="text-center">Factura</th>
        <?php endif; ?>

        <th class="text-end">Monto</th>
        <th></th>
      </tr>
    </thead>

    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $amount         = $row["monto_total"];
        $totalAmount    += $amount;
        $paymentMethod  = [];

        if ($row["efectivo_monto"]        > 0)  $paymentMethod[] = "* Efectivo";
        if ($row["cheque_monto"]          > 0)  $paymentMethod[] = "* Cheque";
        if ($row["transferencia_monto"]   > 0)  $paymentMethod[] = "* Transferencia";
        if ($row["tarjeta_credito_monto"] > 0)  $paymentMethod[] = "* Tarjeta de Crédito";
        if ($row["tarjeta_debito_monto"]  > 0)  $paymentMethod[] = "* Tarjeta de Débito";

        $row["fecha_hora"] = date("d-m-Y", strtotime($row["fecha_hora"]));

        $invoice = getSalePaymentInvoiceBySalePaymentId($row["id_venta_pago"]);
      ?>
        <tr>
          <td><?= $table_row_number++; ?></td>

          <td><?= $row['folio']; ?></td>

          <td><?= $row['fecha_hora_formato']; ?></td>

          <td>
            <?php if (!$row["notas"]) : ?>
              --
            <?php endif; ?>

            <?php if ($row["notas"]) : ?>
              <?= $row["notas"]; ?>
            <?php endif; ?>
          </td>

          <td><?= implode("<br>", $paymentMethod); ?></td>

          <?php if ($hasPPDInvoice) : ?>
            <td class="text-center">
              <?php if (!$invoice) : ?>
                <a class="btn btn-xs btn-secondary" target="_blank" href="<?= BASE_URL; ?>/facturas/nueva?tipo_factura=pago&pago=<?= md5($row["id_venta_pago"]); ?>">Timbrar</a>
              <?php endif; ?>

              <?php if ($invoice) : ?>
                <span class="badge bg-success mb-1">Sí</span><br>

                <?php
                $invoiceFolderUrl = CARPETA_FACTURAS_PAGO_URL;
                ?>

                <a href="<?= $invoiceFolderUrl; ?><?= $invoice['uuid']; ?>.xml" target="_blank">
                  <i class="fa fa-file text-primary"></i> Archivo XML
                </a>
                <br>
                <a href="<?= $invoiceFolderUrl; ?><?= $invoice['uuid']; ?>.pdf" target="_blank">
                  <i class="fa fa-file text-primary"></i> Archivo PDF
                </a>
              <?php endif; ?>
            </td>
          <?php endif; ?>

          <td class="text-end">$<?= number_format($row["monto_total"], DECIMALS_CURRENCY_TICKET); ?></td>

          <td class="text-end">
            <div class="btn-group dropstart">
              <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                <i class="fa fa-ellipsis-v"></i>
              </a>

              <div class="dropdown-menu">
                <a class="dropdown-item" href="<?= BASE_URL; ?>/ticket-pago.php?uid=<?= md5($row['id_venta_pago']); ?>" target="_blank">
                  <i class="fa fa-print"></i>
                  Imprimir ticket
                </a>

                <hr class="my-1">

                <?= getTableActions($identifier, $row); ?>
              </div>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>

      <tr>
        <td colspan="6" class="text-end">
          <h3 class="fs-5 m-0">Total abonado</h3>
        </td>
        <td class="text-end">
          <span class="fw-bold">
            $<?= number_format($totalAmount, DECIMALS_CURRENCY_TICKET); ?>
          </span>
        </td>
        <td></td>
      </tr>
    </tbody>
  </table>
</div>