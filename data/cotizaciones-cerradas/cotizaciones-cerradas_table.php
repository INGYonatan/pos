<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Sucursal</th>
        <th>Cliente</th>
        <th>Realizó</th>
        <th>Estatus</th>
        <th class="text-center">Ediciones</th>
        <th class="text-end">IEPS</th>
        <th class="text-end">IVA</th>
        <th class="text-end">Total</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $productos            = get_quote_details_table($row['id_cotizacion']);
        $row['productos']     = $productos;
        $is_expired           = $fecha_hoy > $row['fecha_expiracion'];

        $advancePaymentsTotal = getQuoteAdvancePaymentsTotal($row["folio"]);

        $quote_totalToPay = doubleval($row["total"]);
        $quote_balance    = $quote_totalToPay - $advancePaymentsTotal;

        $row["quote_totalToPay"] = $quote_totalToPay;
        $row["quote_balance"]    = $quote_balance;

        $saleQuery  = "SELECT id_venta FROM {$db_dti}_ventas WHERE folio_cotizacion = '{$row['folio']}' AND status = 'activo'";
        $saleResult = mysqli_query($mysqli, $saleQuery);
        $saleRows   = mysqli_num_rows($saleResult);

        if ($saleRows > 0) $is_expired = false;
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['folio']; ?>
          </td>

          <td>
            <?= $row['fecha_creacion_formato']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <span class="fw-bold text-dark" href="<?= BASE_URL; ?>/cliente/<?= $row['id_cliente']; ?>/cotizaciones-cerradas"><?= $row['nombre_cliente']; ?></span>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td>
            <?php if (!$is_expired && $row['status'] != 'procesado') : ?>
              <i class="fa fa-check-circle text-info"></i> Vigente
            <?php endif; ?>

            <?php if ($is_expired && $row['status'] != 'procesado') : ?>
              <i class="fa fa-times-circle text-danger"></i> Expirado
            <?php endif; ?>

            <?php if ($row['status'] === 'procesado') : ?>
              <i class="fa fa-check-circle text-success"></i> Procesado
            <?php endif; ?>
          </td>

          <td class="text-center">
            <span class="badge btn-light text-danger">
              <?= $row['ediciones']; ?>
            </span>
          </td>

          <td class="text-end">
            $<?= number_format($row['ieps'] ?? 0, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-end">
            $<?= number_format($row['iva'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-end">
            $<?= number_format($row['total'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?php if (checkModuleActionPermission('pos', 'ver') && !$is_expired && $row['status'] != 'procesado') : ?>
                    <a class="dropdown-item text-success btn-action" target="_blank" href="<?= BASE_URL; ?>/pos?posType=cotizacion-a-venta&uid=<?= $row['id_cotizacion']; ?>">
                      <i class="fa fa-share me-1"></i> Convertir a venta
                    </a>
                  <?php endif; ?>

                  <?php if (checkModuleActionPermission('cotizaciones-agregar-anticipo', 'agregar') && !$is_expired && $row['status'] != 'procesado') :
                    $publicData = htmlentities(json_encode($row));
                  ?>
                    <a class="dropdown-item btn-modal" data-bs-toggle="modal" data-bs-target="#modal-cotizaciones-agregar-anticipo" data-row="<?= $publicData; ?>" href="javascript:void(0)">
                      <i class="fa fa-money-bill-alt me-1"></i> Agregar anticipo
                    </a>
                  <?php endif; ?>

                  <a class="dropdown-item" href="<?= BASE_URL; ?>/ventas?folio=<?= $row['folio']; ?>" target="_blank">
                    <i class="fa fa-eye me-1"></i> Ver anticipos
                  </a>

                  <hr class="my-1">

                  <?php if (checkModuleActionPermission('editar-cotizacion', 'editar') && $row['status'] !== 'procesado') : ?>
                    <a class="dropdown-item" href="<?= BASE_URL; ?>/editar-cotizacion.php?uid=<?= $row['id_cotizacion']; ?>&rollback=cotizaciones-cerradas">
                      <i class="fa fa-pencil-alt"></i> Editar
                    </a>
                  <?php endif; ?>

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

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>