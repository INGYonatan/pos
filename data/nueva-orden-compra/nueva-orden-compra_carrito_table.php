<?php
$table_row_number = 1;
$carrito_total    = 0;

$subtotal         = 0;
$total_ieps       = 0;
$total_iva        = 0;
$total            = 0;
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
          <th style="width: 9rem;">Cantidad</th>
          <th style="width: 9rem;">P. Costo</th>
          <th class="text-center" style="width: 8rem;">Descuento</th>
          <th class="text-center">IEPS</th>
          <th class="text-center">IVA</th>
          <th class="text-end">Total</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $id             = $row['id_producto'];
          $code           = $row['codigo'];
          $name           = $row['nombre_producto'];
          $stock          = $row['stock'];
          $quantity       = $row['cantidad'];
          $have_iva_value = $row['aplica_iva'] ?? false;
          $have_ieps_value = $row['aplica_ieps'] ?? false;
          $have_iva       = ($have_iva_value === true || $have_iva_value === 1 || $have_iva_value === '1' || $have_iva_value === 'si');
          $have_ieps      = ($have_ieps_value === true || $have_ieps_value === 1 || $have_ieps_value === '1' || $have_ieps_value === 'si');
          $iva_percentaje = $row['iva_porcentaje'];
          $ieps_percentage = $row['ieps_porcentaje'] ?? 0;
          $discount_limit = $row['limite_descuento'];
          $entry_unit     = $row['unidad_entrada'];
          $pieces_number  = $row['numero_piezas'];
          $type           = $row['tipo'];
          $serial_numbers = $row['serial_numbers'];

          $price          = $row['precio_costo'];
          $discount       = $row['descuento'];

          # Calcular el precio con descuento
          $price_with_discount = $price - ($price * ($discount / 100));
          $ieps                 = $have_ieps ? ($price_with_discount * ($ieps_percentage / 100)) : 0;
          $iva_base             = $price_with_discount + $ieps;
          $iva                  = $have_iva ? ($iva_base * ($iva_percentaje / 100)) : 0;

          $unit_ieps      = $ieps * $quantity;
          $unit_iva       = $iva * $quantity;
          $unit_subtotal  = $price_with_discount * $quantity;
          $unit_total     = ($price_with_discount + $ieps + $iva) * $quantity;

          $subtotal       += $unit_subtotal;
          $total_ieps     += $unit_ieps;
          $total_iva      += $unit_iva;
          $total          += $unit_total;
        ?>
          <tr class="pos-tr">
            <th scope="row">
              <?= $table_row_number; ?>
            </th>

            <td>
              <?= $code; ?>
            </td>

            <td>
              <?= $name; ?>
            </td>

            <td>
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input id="quantity-<?= $id ?>" class="form-control form-control-sm decimal-input cart-row-input" name="cantidad" value="<?= $quantity; ?>" data-stock="<?= $stock; ?>" data-itemId="<?= $id; ?>" min="1" type="number" autocomplete="off">
                  <div class="input-group-text"><?= $entry_unit; ?></div>
                </div>
              </div>
            </td>

            <td>
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input id="price-<?= $id ?>" class="form-control form-control-sm decimal-input cart-row-input" name="precio_costo" value="<?= $price; ?>" data-itemId="<?= $id; ?>" type="number" autocomplete="off">
                  <div class="input-group-text">$</div>
                </div>
              </div>
            </td>

            <td class="text-center">
              <div class="input-group input-group-sm">
                <input id="discount-<?= $id ?>" class="form-control form-control-sm decimal-input cart-row-input" name="discount" value="<?= $discount; ?>" data-itemId="<?= $id; ?>" data-limit="<?= $discount_limit; ?>" min="0" max="<?= $discount_limit; ?>" type="number" autocomplete="off" <?= $discount_limit == 0 ? 'readonly' : ''; ?>>
                <div class="input-group-text">%</div>
              </div>
            </td>

            <td class="text-center">
              <?= format_decimal_number($ieps_percentage, DECIMALS_CURRENCY_TICKET); ?>%
            </td>

            <td class="text-center">
              <?= format_decimal_number($iva_percentaje, DECIMALS_CURRENCY_TICKET); ?>%
            </td>

            <td class="text-end">
              <span id="cost-price-label-<?= $id; ?>">$<?= number_format(($unit_total), DECIMALS_CURRENCY_TICKET); ?></span>

              <a id="cart-row-action-<?= $identifier; ?>-<?= $id; ?>" class="pos-btn cart-row-action pulse btn-update-row ms-1 me-1" data-itemId="<?= $id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Actualizar fila" href="javascript:void(0)">
                <i class="fa fa-sync text-success" style="font-size: 1.4rem;"></i>
              </a>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $id; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
                <i class="fa fa-times-circle text-danger"></i>
              </a>
            </td>
          </tr>

          <?php $table_row_number++; ?>
        <?php endforeach; ?>

        <tr>
          <td colspan="8" class="text-end fw-bold">Subtotal:</td>
          <td class="text-end">$<?= number_format($subtotal, DECIMALS_CURRENCY_TICKET); ?></td>
        </tr>

        <tr>
          <td colspan="8" class="text-end fw-bold">IEPS:</td>
          <td class="text-end">$<?= number_format($total_ieps, DECIMALS_CURRENCY_TICKET); ?></td>
        </tr>

        <tr>
          <td colspan="8" class="text-end fw-bold">IVA:</td>
          <td class="text-end">$<?= number_format($total_iva, DECIMALS_CURRENCY_TICKET); ?></td>
        </tr>

        <tr>
          <td colspan="8" class="text-end fw-bold">Total:</td>
          <td class="text-end">$<?= number_format($total, DECIMALS_CURRENCY_TICKET); ?></td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    initNumberInput();

    $('.cart-row-input').on('change keyup', e => {
      const itemId = $(e.target).attr('data-itemId');

      $('.cart-row-action').removeClass('active');
      $(`#cart-row-action-<?= $identifier; ?>-${itemId}`).addClass('active');

      if (e.keyCode === 13) {
        const quantity = $(`#quantity-${itemId}`).val();
        const price = $(`#price-${itemId}`).val();
        const discount = $(`#discount-${itemId}`).val();
        const limitDiscount = $(`#discount-${itemId}`).attr('data-limit');

        if (parseFloat(discount) > parseFloat(limitDiscount)) {
          showSweetToast({
            icon: 'warning',
            message: `Ha superado el limite de descuento`
          });
          return;
        }

        updaterow({
          id: itemId,
          quantity,
          price,
          discount
        });
      }
    });

    $('.btn-update-row').on('click', function() {
      const itemId = $(this).attr('data-itemId');
      const quantity = $(`#quantity-${itemId}`).val();
      const price = $(`#price-${itemId}`).val();
      const discount = $(`#discount-${itemId}`).val();
      const limitDiscount = $(`#discount-${itemId}`).attr('data-limit');

      if (parseFloat(discount) > parseFloat(limitDiscount)) {
        showSweetToast({
          icon: 'warning',
          message: `Ha superado el limite de descuento`
        });
        return;
      }

      updaterow({
        id: itemId,
        quantity,
        price,
        discount
      });
    });
  </script>
<?php endif; ?>