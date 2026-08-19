<?php
$table_row_number = (($page - 1) * $per_page) + 1;
$btn_add_target   = "{$products_id}-btn-add";
?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th style="width: 10px;">#</th>
        <th>Código</th>
        <th>Producto</th>
        <th>Existencia</th>
        <th style="width: 8%;">Cantidad</th>
        <th class="text-right">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $quantity_target  = "{$products_id}-quantity-{$row['id_producto']}";
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

          <td>
            <?= $row['stock']; ?>
          </td>

          <td>
            <div class="form-group form-group-sm">
              <input id="<?= $quantity_target; ?>" class="form-control form-control-sm" min="1" max="<?= $row['stock']; ?>" value="1" type="number">
            </div>
          </td>

          <td class="text-right">
            <a class="<?= $btn_add_target; ?>" data-quantityTarget="#<?= $quantity_target; ?>" data-id="<?= $row['id_producto']; ?>" href="javascript:void(0)">
              <i class="fa fa-plus-circle fa-2x"></i>
            </a>
          </td>
        </tr>

        <?php $table_row_number++; ?>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?= paginate($page, $request['num_pages'], 2, 'load'); ?>

<script>
  $('.<?= $btn_add_target; ?>').on('click', function() {
    const id = $(this).attr('data-id');
    const quantityTarget = $(this).attr('data-quantityTarget');
    const quantity = $(quantityTarget).val();

    storeCart.addItem(id, quantity);
  });
</script>