<?php
$table_row_number = 1;
$carrito_total    = 0;
?>

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
          <th style="width: 10rem;">Cantidad</th>
          <th style="width: 10rem;">P. Costo</th>
          <th class="text-end">Total</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $producto_total = $row['cantidad'] * $row['precio_costo'];
          $unit_type      = $row['unidad'] === 'A granel' ? 'kg.' : 'pzs.';

          $carrito_total  = $carrito_total + $producto_total;
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
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-sm decimal-input quantity-input" name="cantidad" value="<?= $row['cantidad']; ?>" data-stock="<?= $row['stock']; ?>" data-itemId="<?= $row['id_producto']; ?>" min="1" type="text">
                  <div class="input-group-text"><?= $unit_type; ?></div>
                </div>
              </div>
            </td>

            <td>
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-sm decimal-input price-input" name="precio_costo" value="<?= $row['precio_costo']; ?>" data-itemId="<?= $row['id_producto']; ?>" type="text">
                  <div class="input-group-text">$</div>
                </div>
              </div>
            </td>

            <td class="text-end">
              <span id="cost-price-label-<?= $row['id_producto']; ?>">$<?= number_format(($row['precio_costo'] * $row['cantidad']), DECIMALS_CURRENCY); ?></span>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $row['id_producto']; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $row['id_producto']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
                <i class="fa fa-times-circle text-danger"></i>
              </a>
            </td>
          </tr>

          <?php $table_row_number++; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <script>
    initNumberInput();

    $('#carrito-total').html(`Total: $<?= number_format($carrito_total, DECIMALS_CURRENCY); ?> MXN`);
  </script>
<?php endif; ?>