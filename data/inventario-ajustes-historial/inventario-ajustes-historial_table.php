<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Folio</th>
        <th>Fecha</th>
        <th>Tipo ajuste</th>
        <th class="text-left">Tipo/Sucursal</th>
        <th>Observaciones</th>
        <th>Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $productos = getStoreSettingsProductsTable($row['id_inventario_ajuste']);
        $row['productos'] = $productos;
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
            <?php if ($row["tipo_ajuste"] != "perdida") : ?>
              <span class="text-capitalize"><?= $row["tipo_ajuste"]; ?></span>
            <?php endif; ?>

            <?php if ($row["tipo_ajuste"] == "perdida") : ?>
              Pérdida
            <?php endif; ?>
          </td>

          <td>
            <div class="d-flex align-item-center justify-content-start gap-1">
              <div>
                <?php if ($row['tipo'] === 'incremento') : ?>
                  <i class="fa fa-fw fa-arrow-up text-success fa-2x"></i>
                <?php endif; ?>

                <?php if ($row['tipo'] === 'decremento') : ?>
                  <i class="fa fa-fw fa-arrow-down text-danger fa-2x"></i>
                <?php endif; ?>
              </div>

              <div>
                <span style="text-transform: capitalize;"><?= $row['tipo']; ?></span><br>
                <?= $row['nombre_sucursal']; ?>
              </div>
            </div>
          </td>

          <td>
            <?php if ($row['observaciones']) : ?>
              <?= $row['observaciones']; ?>
            <?php endif; ?>

            <?php if (!$row['observaciones']) : ?>
              <span class="badge bg-info">Sin observaciones</span>
            <?php endif; ?>
          </td>

          <td>
            <?php if ($row['status'] === 'activo') : ?>
              <span class="badge bg-success"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'cancelado') : ?>
              <span class="badge bg-danger"><?= $row['status']; ?></span>
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

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $per_page,
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>