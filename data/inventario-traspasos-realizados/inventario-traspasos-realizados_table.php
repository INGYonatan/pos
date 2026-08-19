<?php $table_row_number = (($page - 1) * $perPage) + 1; ?>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr class="table-dark">
        <th>#</th>

        <th>Folio</th>

        <th>Fecha</th>

        <?php if ($IS_ADMIN) : ?>
          <th>Sucursal origen</th>
        <?php endif; ?>

        <th>Sucursal destino</th>

        <th>Recibió</th>

        <th>Observaciones</th>

        <th class="text-center">Facturado</th>

        <th class="text-center">Estado</th>

        <?php if ($haveActions) : ?>
          <th class="text-right"></th>
        <?php endif; ?>
      </tr>
    </thead>

    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request["query_result"])) :
        $row["productos"] = getStoreTransferProductsTable($row["id_inventario_transferencia"]);
      ?>
        <tr class="align-middle">
          <td>
            <b><?= $table_row_number++; ?></b>
          </td>

          <td>
            <?= $row["folio"]; ?>
          </td>

          <td>
            <?= $row["fecha_creacion_formato"]; ?>
          </td>

          <?php if ($IS_ADMIN) : ?>
            <td>
              <?= $row["nombre_sucursal_origen"]; ?>
            </td>
          <?php endif; ?>

          <td>
            <?= $row["nombre_sucursal_destino"]; ?>
          </td>

          <td>
            <?php if ($row["recibio"]) : ?>
              <?= $row["recibio"]; ?>
            <?php endif; ?>

            <?php if (!$row["recibio"]) : ?>
              --
            <?php endif; ?>
          </td>

          <td>
            <?php if ($row["observaciones"]) : ?>
              <?= $row["observaciones"]; ?>
            <?php endif; ?>

            <?php if (!$row["observaciones"]) : ?>
              --
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($row["facturado"] == 1) : ?>
              <span class="badge bg-success">Si</span>

              <br>

              <a class="btn btn-light btn-xs rounded-3" href="<?= BASE_URL; ?>/facturas-traspaso?inventoryTransferId=<?= md5($row["id_inventario_transferencia"]); ?>">
                <i class="fa fa-eye me-1"></i> Ver factura
              </a>
            <?php endif; ?>

            <?php if ($row["facturado"] == 0) : ?>
              <span class="badge bg-danger">No</span>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($row["status"] === "activo") : ?>
              <span class="badge bg-info"><?= $row["status"]; ?></span>
            <?php endif; ?>

            <?php if ($row["status"] === "pendiente") : ?>
              <span class="badge bg-warning text-dark"><?= $row["status"]; ?></span>
            <?php endif; ?>

            <?php if ($row["status"] === "cancelado") : ?>
              <span class="badge bg-danger"><?= $row["status"]; ?></span>
            <?php endif; ?>

            <?php if ($row["status"] === "procesado-correctamente") : ?>
              <span class="badge bg-success">Procesado correctamente</span>
            <?php endif; ?>

            <?php if ($row["status"] === "procesado-con-diferencias") : ?>
              <span class="badge bg-info">Procesado con diferencias</span>
            <?php endif; ?>
          </td>

          <?php if ($haveActions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($pageId, $row, [
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
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php renderComponent("table-pagination", [
  "page"      => $page,
  "perPage"   => $perPage,
  "end"       => $table_row_number,
  "numPages"  => $request['num_pages'],
  "total"     => $request['total']
]); ?>