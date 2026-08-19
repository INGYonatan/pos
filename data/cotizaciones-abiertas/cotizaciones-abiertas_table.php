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
        $productos        = get_quote_details_table($row['id_cotizacion']);
        $row['productos'] = $productos;
        $is_expired        = $fecha_hoy > $row['fecha_expiracion'];
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['folio']; ?>
          </td>

          <td>
            <?= $row['fecha_creacion_formato']; ?><br>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <span class="fw-bold text-dark" href="<?= BASE_URL; ?>/cliente/<?= $row['id_cliente']; ?>/cotizaciones-abiertas"><?= $row['nombre_cliente']; ?></span>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td>
            <?php if (!$is_expired) : ?>
              <i class="fa fa-check-circle text-info"></i> Vigente
            <?php endif; ?>

            <?php if ($is_expired) : ?>
              <i class="fa fa-times-circle text-danger"></i> Expirado
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
                  <?php if (checkModuleActionPermission('editar-cotizacion', 'editar')) : ?>
                    <a class="dropdown-item" href="<?= BASE_URL; ?>/editar-cotizacion.php?uid=<?= $row['id_cotizacion']; ?>&rollback=cotizaciones-abiertas">
                      <i class="fa fa-pencil-alt"></i> Editar
                    </a>
                  <?php endif; ?>

                  <?= getTableActions($identifier, $row, [
                    'cancelar' => [
                      'condition' => $row['status'] === 'activo'
                    ]
                  ]); ?>

                  <?php if (checkModuleActionPermission('editar-cotizacion', 'editar') && !$is_expired) : ?>
                    <a class="dropdown-item text-success btn-action" href="javascript:void(0)" data-action="<?= htmlentities(json_encode(['action'  => 'cerrar-cotizacion', 'alert'   => 'si'])); ?>" data-row="<?= htmlentities(json_encode($row)); ?>">
                      <i class="fa fa-share"></i> Cerrar cotización
                    </a>
                  <?php endif; ?>
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