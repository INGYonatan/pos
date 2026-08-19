<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Fecha</th>
        <th>Cantidad</th>
        <th>Producto</th>
        <th>Acción</th>
        <th>Existencia</th>
        <th>Realizó</th>
        <th>Sucursal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : '';
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['fecha_creacion_formato']; ?>
          </td>

          <td>
            <?php if ($row["control_inventario"] == "si") : ?>
              <?= $row['cantidad']; ?> <?= $unit_type; ?>
            <?php endif; ?>

            <?php if ($row["control_inventario"] != "si") : ?>
              <span class="badge bg-secondary">N/A</span>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['nombre_producto']; ?>
          </td>

          <td>
            <?= $row['accion']; ?>
          </td>

          <td>
            <?php if ($row["control_inventario"] == "si") : ?>
              <?= $row['existencia']; ?> <?= $unit_type; ?>
            <?php endif; ?>

            <?php if ($row["control_inventario"] != "si") : ?>
              <span class="badge bg-secondary">N/A</span>
            <?php endif; ?>
          </td>

          <td>
            <?= $row['nombre_completo']; ?>
          </td>

          <td>
            <?= $row['nombre_sucursal']; ?>
          </td>
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