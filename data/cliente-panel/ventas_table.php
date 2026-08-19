<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Sucursal</th>
        <th>Realizó</th>
        <th>Tipo</th>
        <th>Estatus</th>
        <th class="text-right">Subtotal</th>
        <th class="text-right">IVA</th>
        <th class="text-right">Total</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $productos        = get_sale_details_table($row['id_venta']);
        $row['productos'] = $productos;

        $type             = $row["tipo_productos"];
        $type_color       = 'light text-danger';
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['folio']; ?>

            <?php if ($row['folio_cotizacion']) : ?>
              <br>
              <span class="badge bg-info text-dark"><?= $row['folio_cotizacion']; ?></span>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['fecha_creacion_formato']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td>
            <span class="badge bg-<?= $type_color; ?>"><?= $type; ?></span>
          </td>

          <td>
            <?php if ($row['status'] === 'activo') : ?>
              <i class="fa fa-check-circle text-success"></i> Activo
            <?php endif; ?>

            <?php if ($row['status'] === 'cancelado') : ?>
              <i class="fa fa-check-circle text-danger"></i> Cancelado
            <?php endif; ?>
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

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($ventas_tid, $row, [
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