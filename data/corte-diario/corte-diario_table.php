<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="col-12 col-lg-9">
  <div class="card card-border-top">
    <div class="card-body">
      <h4 class="header-title mb-3">Ventas del día</h4>

      <?php if ($request['status'] === 'error') : ?>
        <?= getEmptyTableMessage(); ?>
      <?php endif; ?>

      <?php if ($request['status'] === 'success') : ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr class="">
                <th style="width: 10px;">#</th>
                <th>Folio</th>
                <th>Cliente</th>
                <th>Método de pago</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Total</th>
                <th class="text-center">Estatus</th>

                <?php if ($have_actions) : ?>
                  <th class="text-right">Acciones</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
                $type             = sale_get_product_types_for_sale($row['id_venta']);
                $type_color       = 'light text-danger';

                if ($type == 'Equipo')       $type_color = 'danger';
                if ($type == 'Llantas')      $type_color = 'primary text-dark';
                if ($type == 'Rines')        $type_color = 'info text-dark';
                if ($type == 'Refacciones')  $type_color = 'warning text-dark';
                if ($type == 'Servicios')    $type_color = 'success text-dark';
                if ($type == 'Otros')        $type_color = 'secondary text-dark';
                if ($type == 'Mixto')        $type_color = 'dark text-white';

                $metodoPago = "-";

                if ($row["efectivo"] > 0)         $metodoPago = "<p>* Efectivo</p>";
                if ($row["cheque"] > 0)           $metodoPago = "<p>* Cheque</p>";
                if ($row["transferencia"] > 0)    $metodoPago = "<p>* Transferencia</p>";
                if ($row["tarjeta_credito"] > 0)  $metodoPago = "<p>* Tarjeta de crédito</p>";
                if ($row["tarjeta_debito"] > 0)   $metodoPago = "<p>* Tarjeta de débito</p>";

                $productos        = get_sale_details_table($row['id_venta']);

                $tableProductos = "<h4>Método de pago:</h4>";
                $tableProductos .= $metodoPago;
                $tableProductos .= $productos;
                $row['productos'] = $tableProductos;
              ?>
                <tr>
                  <th scope="row">
                    <?= $table_row_number; ?>
                  </th>

                  <td>
                    <?= $row['folio']; ?>

                    <?php if ($row['folio_cotizacion']) : ?>
                      <br>
                      <span class="badge bg-info text-dark" title="Folio de cotización"><?= $row['folio_cotizacion']; ?></span>
                    <?php endif; ?>
                  </td>

                  <td>
                    <a class="fw-bold text-primary" href="<?= BASE_URL; ?>/cliente/<?= $row['id_cliente']; ?>/ventas"><?= $row['nombre_cliente']; ?></a>
                  </td>

                  <td>
                    <?= $metodoPago; ?>
                  </td>

                  <td class="text-right">
                    $<?= number_format($row['subtotal'], DECIMALS_CURRENCY); ?>
                  </td>

                  <td class="text-right">
                    $<?= number_format($row['iva'], DECIMALS_CURRENCY); ?>
                  </td>

                  <td class="text-right">
                    $<?= number_format($row['total'], DECIMALS_CURRENCY); ?>
                  </td>

                  <td class="text-center">
                    <?php if ($row['status'] === 'activo') : ?>
                      <i class="fa fa-check-circle text-success"></i> Activo
                    <?php endif; ?>

                    <?php if ($row['status'] === 'cancelado') : ?>
                      <i class="fa fa-check-circle text-danger"></i> Cancelado
                    <?php endif; ?>
                  </td>

                  <?php if ($have_actions) : ?>
                    <td class="text-right">
                      <div class="btn-group dropstart">
                        <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                          <i class="fa fa-ellipsis-v"></i>
                        </a>

                        <div class="dropdown-menu">
                          <?= getTableActions($identifier, $row, [
                            'cancelar' => [
                              'condition' => $row['status'] === 'activo'
                            ]
                          ]); ?>
                        </div>
                      </div>
                    </td>
                  <?php endif; ?>
                </tr>

                <?php $table_row_number++; ?>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <?= paginate($page, $request['num_pages'], 2, 'load'); ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php
$totalAmountInCash        = getTotalAmountInCash($fecha);
$totalAmountInDebitCard   = getTotalAmountInDebitCard($fecha);
$totalAmountInCreditCard  = getTotalAmountInCreditCard($fecha);
$totalAmountInTransfer    = getTotalAmountInTransfer($fecha);
$totalAmountInMoneyCheck  = getTotalAmountInMoneyCheck($fecha);
$totalAmount              = $totalAmountInCash + $totalAmountInDebitCard + $totalAmountInCreditCard + $totalAmountInTransfer + $totalAmountInMoneyCheck;
?>

<div class="col-12 col-lg-3">
  <div class="row">
    <div class="col-12">
      <div class="card card-border-top border-top-success">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fa fa-dollar-sign text-success" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">Total</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmount, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-border-top">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fa fa-money-bill-alt text-dark" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">Efectivo</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmountInCash, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-border-top">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fa fa-credit-card text-dark" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">T. Débito</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmountInDebitCard, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-border-top">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fab fa-cc-visa text-dark" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">T. Crédito</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmountInCreditCard, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-border-top">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fa fa-exchange-alt text-dark" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">Transferencia</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmountInTransfer, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card card-border-top">
        <div class="card-body d-flex flex-row align-items-center justify-content-between p-2">
          <div>
            <div class="avatar-md">
              <div class="avatar-title border-1 bg-light rounded-circle">
                <i class="fa fa-money-check text-dark" style="font-size: 1.2rem;"></i>
              </div>
            </div>
          </div>

          <div class="text-end d-flex flex-column gap-1">
            <h4 class="m-0">Cheque</h4>
            <h5 class="m-0 text-muted">$<?= number_format($totalAmountInMoneyCheck, DECIMALS_CURRENCY); ?></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>