<?php $table_row_number = (($page - 1) * $per_page) + 1;
$btn_add_target   = "{$identifier}-btn-add";
?>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th></th>
        <th>Producto</th>
        <th>Stock</th>

        <?php if ($have_actions) : ?>
          <th class="text-right">Acciones</th>
        <?php endif; ?>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($request['query_result'])) :
        $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : '';
        $quantity_target  = "{$identifier}-quantity-{$row['id_producto']}";
      ?>
        <tr>
          <td>
            <a class="<?= $btn_add_target; ?>" data-quantityTarget="#<?= $quantity_target; ?>" data-id="<?= $row['id_producto']; ?>" href="javascript:void(0)">
              <i class="fa fa-plus-circle"></i>
            </a>
          </td>

          <td>
            <?= $row['nombre_producto']; ?>
          </td>

          <td>
            <?php if ($row['stock'] == 0) : ?>
              <span class="badge bg-danger">Sin stock</span>
            <?php endif; ?>

            <?php if ($row['stock'] > 0) : ?>
              <?= $row['stock']; ?> <?= $unit_type; ?>
            <?php endif; ?>
          </td>

          <?php if ($have_actions) : ?>
            <td class="text-right">
              <div class="btn-group dropstart">
                <a class="btn btn-sm btn-soft-primary px-1 dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" href="javascript:void(0)">
                  <i class="fa fa-ellipsis-v"></i>
                </a>

                <div class="dropdown-menu">
                  <?= getTableActions($identifier_inventario, $row); ?>
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

<?= paginate($page, $request['num_pages'], 2, 'load'); ?>

<script>
  $('.<?= $btn_add_target; ?>').on('click', function() {
    const id = $(this).attr('data-id');
    //const quantityTarget = $(this).attr('data-quantityTarget');
    //const quantity = $(quantityTarget).val();

    storeCart.addItem(id, 1);
  });
</script>