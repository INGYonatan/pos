<?php $table_row_number = (($page - 1) * $per_page) + 1; ?>

<div class="table-responsive">
  <table class="table table-sm">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Código</th>
        <th>Producto</th>
        <th class="text-center">Existencia</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $historial_url = BASE_URL . "/kardex?uid={$row['id_producto']}";
        if (!empty($id_sucursal)) $historial_url .= "&sid={$id_sucursal}";
        $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : '';
      ?>
        <tr>
          <th scope="row">
            <?= $table_row_number; ?>
          </th>

          <td>
            <?= $row['codigo']; ?>
          </td>

          <td>
            <?= $row['nombre_producto']; ?>
          </td>

          <td class="text-center <?= $row['existencia'] <= '0' ? 'bg-danger text-white' : 'bg-success text-dark'; ?>">
            <?= $row['existencia']; ?> <?= $unit_type; ?>
          </td>
        </tr>

        <?php $table_row_number++; ?>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?= paginate($page, $request['num_pages'], 2, 'load'); ?>