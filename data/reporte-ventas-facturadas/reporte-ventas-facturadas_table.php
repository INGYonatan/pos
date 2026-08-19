<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<?php if ($result["status"] == "error") : ?>
  <?= getEmptyTableMessage(); ?>
<?php endif; ?>

<?php if ($result["status"] == "success") : ?>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Fecha</th>
          <th>Sucursal</th>
          <th>Cliente</th>
          <th class="text-center">Tipo</th>
          <th class="text-center">Referencia</th>
          <th class="text-end">Importe</th>
        </tr>
      </thead>

      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result["query_result"])) : ?>
          <tr>
            <td>
              <?= $row["folio"]; ?>

              <br>
              <span class="text-muted" style="font-size: 0.85em;"><?= $row["folio_factura"]; ?></span>
            </td>

            <td>
              <?= dateToSpanishStructure($row["fecha_creacion"]); ?>
            </td>

            <td>
              <?= $row["nombre_sucursal"]; ?>
            </td>

            <td>
              <?= $row["nombre_cliente"]; ?>
            </td>

            <td class="text-center">
              <span class="badge bg-success"><?= $row["tipo_productos"]; ?></span>
            </td>

            <td class="text-center">
              <?php if ($row["efectivo_referencia"]) : ?>
                <?= $row["efectivo_referencia"]; ?>
              <?php endif; ?>

              <?php if (!$row["efectivo_referencia"]) : ?>
                --
              <?php endif; ?>
            </td>

            <td class="text-end">
              <table class="table table-xs m-0">
                <tbody>
                  <?php if ($row["efectivo"] > 0) : ?>
                    <tr>
                      <td class="text-end">Efec:</td>
                      <td class="text-end">$<?= number_format($row["efectivo"], DECIMALS_CURRENCY_TICKET); ?></td>
                    </tr>
                  <?php endif; ?>

                  <?php if ($row["cheque"] > 0) : ?>
                    <tr>
                      <td class="text-end">Cheq:</td>
                      <td class="text-end">$<?= number_format($row["cheque"], DECIMALS_CURRENCY_TICKET); ?></td>
                    </tr>
                  <?php endif; ?>

                  <?php if ($row["transferencia"] > 0) : ?>
                    <tr>
                      <td class="text-end">Trans:</td>
                      <td class="text-end">$<?= number_format($row["transferencia"], DECIMALS_CURRENCY_TICKET); ?></td>
                    </tr>
                  <?php endif; ?>

                  <?php if ($row["tarjeta_credito"] > 0) : ?>
                    <tr>
                      <td class="text-end">Créd:</td>
                      <td class="text-end">$<?= number_format($row["tarjeta_credito"], DECIMALS_CURRENCY_TICKET); ?></td>
                    </tr>
                  <?php endif; ?>

                  <?php if ($row["tarjeta_debito"] > 0) : ?>
                    <tr>
                      <td class="text-end">Déb:</td>
                      <td class="text-end">$<?= number_format($row["tarjeta_debito"], DECIMALS_CURRENCY_TICKET); ?></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </td>
          </tr>

          <?php $table_row_number++; ?>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => $table_row_number,
  "numPages"  => $result["num_pages"],
  "total"     => $result["total"]
]); ?>

<?php
// Total por depositar
$totalToDeposit           = getSaleCashTotalToDeposit();
$totalToDepositFormatted  = number_format($totalToDeposit, DECIMALS_CURRENCY_TICKET);

// Total por depositar por fecha
$totalToDepositByDate           = getSaleCashTotalToDeposit($date);
$totalToDepositByDateFormatted  = number_format($totalToDepositByDate, DECIMALS_CURRENCY_TICKET);

$ticketLink = BASE_URL . "/ticket-reporte-ventas-facturadas";

$stats = <<<HTML
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total por depositar">Total por depositar</h5>
              <h3 class="my-2 py-1"><span data-plugin="counterup">$ {$totalToDepositFormatted}</span></h3>
            </div>

            <div class="avatar-sm">
              <span class="avatar-title bg-primary rounded">
                <i class="mdi mdi-cash-multiple font-24"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <a class="btn btn-white w-100 mb-3" target="_blank" href="{$ticketLink}">
        <i class="fa fa-download me-2"></i>
        DESCARGAR MES
        <i class="fa fa-file ms-2"></i>
      </a>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="form-group">
        <input id="dateInput" class="form-control" value="{$date}" type="date">
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <div>
              <h5 class="text-muted fw-normal mt-0 text-truncate" title="Por depositar del día">Por depositar del día</h5>
              <h3 class="my-2 py-1"><span data-plugin="counterup">$ {$totalToDepositByDateFormatted}</span></h3>
            </div>

            <div class="avatar-sm">
              <span class="avatar-title bg-primary rounded">
                <i class="mdi mdi-cash-multiple font-24"></i>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
HTML;
?>

<script>
  $('#stats-container').html(`<?= $stats; ?>`);

  $("#dateInput").on("change", function() {
    $("#filter-date").val($(this).val()).closest("form").submit();
  });
</script>