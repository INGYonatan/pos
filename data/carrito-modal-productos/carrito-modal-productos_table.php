<?php
$table_row_number = (($page - 1) * $per_page) + 1;
$btn_add_target   = "{$identifier}-btn-add";
?>

<div class="table-responsive">
  <table class="table table-hover">
    <thead>
      <tr class="">
        <th style="width: 10px;">#</th>
        <th>Código</th>
        <th>Producto</th>
        <th class="text-center">Existencia</th>
        <th style="width: 8%;">Cantidad</th>
        <th class="text-right">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $quantity_target  = "{$identifier}-quantity-{$row['id_producto']}";
        $useLimitQuantityRow = $row["control_inventario"] == "no" ? "no" : $useLimitQuantity;
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

          <td class="text-center">
            <?php if ($row["control_inventario"] == "si") : ?>
              <?= $row['stock']; ?>
            <?php endif; ?>

            <?php if ($row["control_inventario"] != "si") : ?>
              <span class="badge bg-secondary">N/A</span>
            <?php endif; ?>
          </td>

          <td>
            <div class="form-group form-group-sm">
              <?php
              $quantityMaxAttr = $useLimitQuantityRow == "si" ? "max='{$row['stock']}'" : "";
              ?>
              <input id="<?= $quantity_target; ?>" class="form-control form-control-sm" min="1" <?= $quantityMaxAttr; ?> value="1" type="number">
            </div>
          </td>

          <td class="text-right">
            <a class="btn btn-light <?= $btn_add_target; ?>" data-quantityTarget="#<?= $quantity_target; ?>" data-id="<?= $row['id_producto']; ?>" href="javascript:void(0)">
              <!-- <i class="fa fa-plus-circle fa-2x"></i> -->
              <i class="fa fa-plus me-1"></i> Agregar
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

  $(`[data-modalProductosBranchId]`).on('change', function() {
    const branchId = $(this).val();

    $('#<?= $identifier; ?>-branchId').val(branchId);
  });
</script>