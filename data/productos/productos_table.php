<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-hover" style="font-size: 0.8rem;">
    <thead>
      <tr>
        <th>#</th>
        <th>Producto</th>
        <th>Marca</th>
        <!-- <th>Línea</th>
        <th>Familia</th> -->
        <th>Proveedor</th>
        <th>Tipo</th>
        <th>Presentación</th>
        <th class="text-center">Control de Inventario</th>
        <th class="text-end">P. Costo</th>
        <th class="text-end">P. Venta</th>
        <th class="text-center">Estatus</th>

        <?php if ($have_actions) : ?>
          <th class="text-end"></th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) : ?>
        <tr>
          <td>
            <?= $row['codigo']; ?>
          </td>

          <td>
            <?= $row['nombre_producto']; ?>
          </td>

          <td>
            <?= $row['marca']; ?>
          </td>

          <!-- <td>
            <?= $row['categoria']; ?>
          </td>

          <td>
            <?= $row['familia']; ?>
          </td> -->

          <td>
            <?= $row['proveedor']; ?>
          </td>

          <td>
            <?php
            $badge_color = 'bg-secondary'; // Color por defecto
            if ($row['tipo'] == 'equipo') $badge_color = 'bg-danger';
            if ($row['tipo'] == 'llantas') $badge_color = 'bg-primary';
            if ($row['tipo'] == 'rines') $badge_color = 'bg-info';
            if ($row['tipo'] == 'refacciones') $badge_color = 'bg-warning';
            if ($row['tipo'] == 'servicios') $badge_color = 'bg-success';
            if ($row['tipo'] == 'otros') $badge_color = 'bg-secondary';
            ?>
            <span class="text-capitalize badge <?= $badge_color; ?>"><?= $row['tipo']; ?></span>
          </td>

          <td>
            <?= $row['unidad_entrada']; ?>
          </td>

          <td class="text-center">
            <?= $row['control_inventario'] == 'si' ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>'; ?>
          </td>

          <td class="text-end">
            $<?= number_format($row['precio_costo_original'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-end">
            $<?= number_format($row['precio_venta_original'], DECIMALS_CURRENCY_TICKET); ?>
          </td>

          <td class="text-center">
            <span class="badge bg-<?= $row['status'] == 'activo' ? 'success' : 'danger'; ?>"><?= $row['status']; ?></span>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-end">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier, $row, [
                    "eliminar" => [
                      "condition" => $row['status'] == 'activo'
                    ],
                    "activar" => [
                      "condition" => $row['status'] == 'eliminado'
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

<script>
  $('.btn-edit').on('click', function() {
    const data = JSON.parse($(this).attr('data-row'));

    getFamilies(data.id_categoria, data.id_categoria_familia);
    getBrandCategories(data.id_marca, data.id_categoria);
    handleToggleInputUnit(data.unidad_entrada);
  });
</script>