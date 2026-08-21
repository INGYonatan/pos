<?php

/**
 * @var string $identifier
 * @var int $page
 * @var int $perPage
 * @var array $result
 * @var string $db_dti
 */

$rowCounter = (($page - 1) * $perPage) + 1;

?>

<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
        <th>#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Sucursal</th>
        <th class="text-end">Total</th>
        <th class="text-center">Estatus</th>
        <th></th>
      </tr>
    </thead>

    <tbody>
      <?php while ($row = mysqli_fetch_assoc($result["query_result"])) :
        // Obtener el total a pagar de la venta
        $totalAmount      = getSaleTotalById($row["id_venta"]);
        $totalPaid        = getSaleTotalPaidById($row["id_venta"]);
        $balance          = round($totalAmount - $totalPaid, DECIMALS_CURRENCY);

        $salesTotalAmount += floatval($totalAmount);

        $row["sale_totalToPay"] = $totalAmount;
        $row["sale_totalPaid"]  = $totalPaid;
        $row["sale_balance"]    = $balance;
        $row["sale_products"]   = get_sale_details_table($row["id_venta"]);

        $dataRow = [
          "id_venta"        => $row["id_venta"],
          "sale_totalToPay" => $row["sale_totalToPay"],
          "sale_totalPaid"  => $row["sale_totalPaid"],
          "sale_balance"    => $row["sale_balance"],
          "sale_products"   => $row["sale_products"]
        ];

        // Obtener todos los pagos realizados
        $queryPayments  = "SELECT * FROM {$db_dti}_venta_pagos WHERE id_venta = ? ORDER BY id_venta_pago DESC";
        $stmt           = $mysqli->prepare($queryPayments);

        $stmt->bind_param("i", $row["id_venta"]);
        $stmt->execute();

        $resultPayments = $stmt->get_result();
        $numPayments    = $resultPayments->num_rows;

        ob_start();

        if ($numPayments == 0) echo "<div class='alert alert-info mb-0' role='alert'>No hay pagos realizados en esta venta.</div>";

        if ($numPayments > 0) {
          $paymentsRowCounter = 1;
      ?>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Folio</th>
                  <th>Fecha</th>
                  <th>Notas</th>
                  <th class="text-end">Efectivo</th>
                  <th class="text-end">Cheque</th>
                  <th class="text-end">Transferencia</th>
                  <th class="text-end">Tarjeta de crédito</th>
                  <th class="text-end">Tarjeta de débito</th>
                  <th class="text-end">Total pagado</th>
                  <th class="no-print"></th>
                </tr>
              </thead>

              <tbody>
                <?php while ($payment = $resultPayments->fetch_assoc()) : ?>
                  <tr class="align-middle">
                    <td>
                      <?= $paymentsRowCounter; ?>
                    </td>

                    <td>
                      <?= $payment["folio"]; ?>
                    </td>

                    <td>
                      <?= $payment["fecha_hora"]; ?>
                    </td>

                    <td>
                      <?= $payment["notas"] ? $payment["notas"] : "--"; ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["efectivo_monto"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["cheque_monto"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["transferencia_monto"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["tarjeta_credito_monto"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["tarjeta_debito_monto"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end">
                      $<?= number_format($payment["monto_total"], DECIMALS_CURRENCY_TICKET); ?>
                    </td>

                    <td class="text-end no-print">
                      <div class="btn-group">
                        <a class="btn btn-primary btn-xs p-1" target="_blank" href="<?= BASE_URL; ?>/ticket-pago?uid=<?= md5($payment["id_venta_pago"]); ?>">
                          <i class="fas fa-print"></i>
                        </a>

                        <button class="btn btn-danger btn-xs p-1 btn-delete-payment" data-uid="<?= $payment["id_venta_pago"]; ?>" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar pago">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </div>
                    </td>
                  </tr>

                  <?php $paymentsRowCounter++; ?>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        <?php
        }

        $paymentsTable = ob_get_clean();

        $dataRow["sale_paymentsTable"] = $paymentsTable;

        // Obtener el saldo pendiente
        $pendingBalance = $salesTotalAmount - $paymentsTotalAmount;
        ?>
        <tr class="align-middle">
          <td>
            <?= $rowCounter; ?>
          </td>

          <td>
            <a class="fw-bold btn-show-pagos" data-bs-toggle="modal" data-bs-target="#<?= $identifier; ?>-modal-ver-pagos" data-row="<?= htmlentities(json_encode($dataRow)) ?>" href="javascript:void(0)">
              <?= $row["folio"]; ?>
            </a>
          </td>

          <td>
            <?= $row["fecha_creacion_formato"]; ?>
          </td>

          <td>
            <span class="fw-bold text-dark" href="<?= BASE_URL; ?>/sucursal/<?= $row["id_sucursal"]; ?>/ventas"><?= $row["nombre_sucursal"]; ?></span>
          </td>

          <td class="text-end">
            $<?= number_format($row["total"], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-center">
            <?php if ($row["pagado"] == "si") : ?>
              <span class="badge bg-success">Pagado</span>
            <?php else : ?>
              <span class="badge bg-warning text-dark">Pendiente</span>
            <?php endif; ?>
          </td>

          <td class="text-end" width="15%">
            <div class="btn-group">
              <?php if ($row["pagado"] == "no") : ?>
                <button class="btn btn-primary btn-xs m-0 btn-add btn-add-payment btn-modal" data-row="<?= htmlentities(json_encode($dataRow)) ?>" data-bs-toggle="modal" data-bs-target="#ventas-pagos-modal" type="button">
                  <small>
                    <i class="fas fa-plus me-1"></i>
                    Nuevo pago
                  </small>
                </button>
              <?php endif; ?>

              <button class="btn btn-light btn-xs m-0 btn-show-products" data-row="<?= htmlentities(json_encode($dataRow)) ?>" data-bs-toggle="modal" data-bs-target="#<?= $identifier; ?>-modal-ver-productos" type="button">
                <small>
                  <i class="fas fa-eye me-1"></i>
                  Ver Productos
                </small>
              </button>

              <a class="btn btn-light btn-xs m-0" target="_blank" href="<?= BASE_URL; ?>/ticket-venta?uid=<?= $row["id_venta"]; ?>">
                <small>
                  <i class="fas fa-print"></i>
                  Imprimir
                </small>
              </a>
            </div>
          </td>
        </tr>

        <?php $rowCounter++; ?>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $perPage,
  "end"       => $rowCounter,
  "numPages"  => $result["num_pages"],
  "total"     => $result["total"]
]); ?>