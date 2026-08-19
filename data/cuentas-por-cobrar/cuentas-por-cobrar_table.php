<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Cliente</th>
        <th class="text-end">Total</th>
        <th class="text-end">Abonado</th>
        <th class="text-end">Saldo</th>
        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $customerId = $row['id_cliente'];

        // Obtener el total de las ventas a crédito activas y no pagadas
        $creditSaleTotal = getCreditSaleTotalByCustomerId($customerId, $branchId);

        // Obtener el total pagado
        $totalPaid = getTotalBalancePaidByCustomerId($customerId, $branchId);

        // Obtener el saldo pendiente
        $balance = $creditSaleTotal - $totalPaid;
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <a class="text-link fw-bold" href="<?= BASE_URL; ?>/cuentas-por-cobrar/<?= md5($row["id_cliente"]); ?>/estado-de-cuenta">
              <?= $row["cliente_nombre"]; ?>
            </a>
          </td>

          <td class="text-end">
            $<?= number_format($creditSaleTotal, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-end">
            $<?= number_format($totalPaid, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-end">
            $<?= number_format($balance, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <a class="dropdown-item" href="<?= BASE_URL; ?>/cuentas-por-cobrar/<?= md5($row["id_cliente"]); ?>/estado-de-cuenta">
                    <i class="fa fa-file-alt dropdown-item-icon"></i> Ver estado de cuenta
                  </a>

                  <!-- <a class="dropdown-item" href="javascript:void(0)">
                    <i class="fa fa-print dropdown-item-icon"></i> Imprimir estado de cuenta
                  </a> -->
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

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>