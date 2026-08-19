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
        <th class="text-end">Subtotal</th>
        <th class="text-end">IEPS</th>
        <th class="text-end">Iva</th>
        <th class="text-end">Total</th>
        <th class="text-center">Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $productos = purchase_order_details_table($row['id_orden_compra']);
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
            <?= $row['nombre_sucursal']; ?>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td class="text-right">
            $<?= number_format($row['subtotal'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            $<?= number_format($row['ieps'] ?? 0, DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            $<?= number_format($row['iva'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-right">
            $<?= number_format($row['total'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-center">
            <?php if ($row['status'] === 'activo') : ?>
              <span class="badge bg-success"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'cancelado') : ?>
              <span class="badge bg-danger"><?= $row['status']; ?></span>
            <?php endif; ?>

            <?php if ($row['status'] === 'comprado') : ?>
              <span class="badge bg-info"><?= $row['status']; ?></span>
            <?php endif; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?php if ($row["status"] == "activo") : ?>
                    <a class="dropdown-item" href="<?= BASE_URL; ?>/nueva-compra?ocid=<?= md5($row["id_orden_compra"]); ?>">
                      <i class="fa fa-shopping-cart"></i> Convertir a compra
                    </a>

                    <hr class="my-1">
                  <?php endif; ?>

                  <?php if (checkModuleActionPermission('editar-orden-compra', 'editar') && $row['status'] === 'activo') : ?>
                    <a class="dropdown-item" href="<?= BASE_URL; ?>/ordenes-compra/<?= $row['id_orden_compra']; ?>/editar">
                      <i class="fa fa-pencil-alt"></i> Editar
                    </a>
                  <?php endif; ?>

                  <a class="dropdown-item" target="_blank" href="<?= BASE_URL; ?>/pdf-orden-compra?uid=<?= $row["uid"]; ?>">
                    <i class="fa fa-print"></i> Imprimir
                  </a>

                  <?= getTableActions($identifier, $row, [
                    'cancelar' => [
                      'condition' => $row['status'] === 'activo'
                    ],
                    'convertir-a-compra' => [
                      'condition' => false
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