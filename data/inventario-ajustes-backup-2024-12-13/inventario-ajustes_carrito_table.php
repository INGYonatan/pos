<?php $table_row_number = 1; ?>

<?php if (isEmptyArray($carrito)) : ?>
  <div class="p-2">
    El carrito está vacío
  </div>
<?php endif; ?>

<?php if (!isEmptyArray($carrito)) : ?>
  <div class="">
    <table class="table table-sm table-hover">
      <thead>
        <tr class="table-dark">
          <th style="width: 10px;">#</th>
          <th>Código</th>
          <th>Producto</th>
          <th>Stock incial</th>
          <th style="width: 8rem;">Cantidad</th>
          <th>Stock final</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $unit_type = $row['unidad'] === 'A granel' ? 'kg.' : '';
        ?>
          <tr class="pos-tr">
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
              <?= $row['stock']; ?> <?= $unit_type; ?>
            </td>

            <td>
              <div class="form-group mb-0">
                <input class="form-control form-control-sm decimal-input quantity-input" name="cantidad" value="<?= $row['cantidad']; ?>" data-stock="<?= $row['stock']; ?>" data-itemId="<?= $row['id_producto']; ?>" min="1" type="text">
              </div>
            </td>

            <td>
              <span id="new-stock-label-<?= $row['id_producto']; ?>"><?= $tipo_movimiento == TIPO_MOVIMIENTO_DECREMENTO ? ($row['stock'] - $row['cantidad']) : ($row['stock'] + $row['cantidad']); ?></span> <?= $unit_type; ?>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $row['id_producto']; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $row['id_producto']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
                <i class="fa fa-times-circle text-danger"></i>
              </a>
            </td>

            <!-- <td class="text-right">
              <button class="btn btn-sm btn-danger ms-3 btn-remove-item" data-itemId="<?= $row['id_producto']; ?>" type="button">
                <i class="fa fa-times"></i>
              </button>

              <button class="btn btn-sm">
                <span id="cart-row-<?= $identifier; ?>-loading-<?= $row['id_producto']; ?>" class="spinner-border spinner-border-sm opacity-0" aria-hidden="true"></span>
              </button>
            </td> -->
          </tr>

          <?php $table_row_number++; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    initNumberInput();
  </script>
<?php endif; ?>