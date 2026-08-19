<?php
$table_row_number = 1;
$carrito_total    = 0;
?>

<?php if (isEmptyArray($carrito)) : ?>
  <div class="p-2">
    El carrito está vacío
  </div>

  <script>
    $('#carrito-total').html(`Total: $0.00 MXN`);
  </script>
<?php endif; ?>

<?php if (!isEmptyArray($carrito)) : ?>
  <div class="table-responsives">
    <table class="table table-sm table-hover">
      <thead>
        <tr class="table-dark">
          <th>Producto</th>
          <th>Precio</th>
          <th style="width: 15%;">Cantidad</th>
          <th class="text-right">Precio Neto</th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $producto_total = $row['cantidad'] * $row['precio_venta'];
          $carrito_total  = $carrito_total + $producto_total;
        ?>
          <tr class="pos-tr">
            <td>
              <?= $row['nombre_producto']; ?>
            </td>

            <td>
              $<?= number_format($row['precio_venta'], DECIMALS_CURRENCY); ?>
            </td>

            <td>
              <div class="form-group mb-0">
                <input class="form-control form-control-sm number-input quantity-input" name="cantidad" value="<?= $row['cantidad']; ?>" data-stock="<?= $row['stock']; ?>" data-itemId="<?= $row['id_producto']; ?>" min="1" type="number">
              </div>
            </td>

            <td class="text-right">
              <span id="total-<?= $row['id_producto']; ?>">$<?= number_format($producto_total, DECIMALS_CURRENCY) ?></span>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $row['id_producto']; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $row['id_producto']; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
                <i class="fa fa-times-circle text-danger"></i>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<script>
  initNumberInput();

  $('#carrito-total').html(`Total: $<?= number_format($carrito_total, DECIMALS_CURRENCY); ?> MXN`);
  $('#atc-pago_con').val(<?= $carrito_total ?>);
  $('#atc-total').val(<?= $carrito_total ?>);
  calcularCambio(<?= $carrito_total ?>, <?= $carrito_total ?>);

  $('[data-bs-toggle="tooltip"]').tooltip();
</script>