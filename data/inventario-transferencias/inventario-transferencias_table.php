<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Sucursal origen</th>
        <th>Sucursal destino</th>
        <th>Observaciones</th>
        <th class="text-center">Facturado</th>
        <th>Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $productos = getStoreTransferProductsTable($row['id_inventario_transferencia']);
        $row['productos'] = $productos;
      ?>
        <tr class="align-middle">
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
            <?= $row['nombre_sucursal_origen']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal_destino']; ?>
          </td>

          <td>
            <?php if ($row['observaciones']) : ?>
              <?= $row['observaciones']; ?>
            <?php endif; ?>

            <?php if (!$row['observaciones']) : ?>
              <span class="badge bg-info">Sin observaciones</span>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($row['facturado'] == 1) : ?>
              <span class="badge bg-success">Si</span>
              <br>
              <a class="btn btn-light btn-xs rounded-3" href="<?= BASE_URL; ?>/facturas-traspaso?inventoryTransferId=<?= md5($row['id_inventario_transferencia']); ?>">
                <i class="fa fa-eye me-1"></i> Ver factura
              </a>
            <?php endif; ?>

            <?php if ($row['facturado'] == 0) : ?>
              <span class="badge bg-danger">No</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($row['status'] === 'pendiente') : ?>
              <span class="badge bg-warning text-dark"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'activo') : ?>
              <span class="badge bg-info"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'cancelado') : ?>
              <span class="badge bg-danger"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'completado') : ?>
              <span class="badge bg-success"><?= $row['status']; ?></span>
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
                      'condition' => $row['status'] !== 'cancelado' ? true : false
                    ],
                    "generar-factura" => [
                      "condition" => $row["facturado"] == 0
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