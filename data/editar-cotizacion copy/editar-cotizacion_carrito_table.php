<?php $table_row_number = 1; ?>

<?php if (isEmptyArray($list)) : ?>
  <div class="p-2">
    El carrito está vacío
  </div>
<?php endif; ?>

<?php if (!isEmptyArray($list)) : ?>
  <div class="">
    <table class="table table-sm table-hover">
      <thead>
        <tr class="table-dark">
          <th style="width: 10px;">#</th>
          <th>Código</th>
          <th>Producto</th>
          <th style="width: 10rem;">Cantidad</th>
          <th class="text-end" style="width: 10rem;">P. Venta</th>
          <th class="text-end" style="width: 10rem;">Importe</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $key => $product) : ?>
          <tr class="pos-tr">
            <td class="align-middle">
              <b><?= $table_row_number; ?></b>
            </td>

            <td class="align-middle">
              <?= $product->code; ?>
            </td>

            <td class="align-middle">
              <?= $product->name; ?>
            </td>

            <td class="align-middle">
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-sm decimal-input quantity-input" name="cantidad" value="<?= $product->quantity; ?>" data-stock="<?= $product->stock; ?>" data-itemId="<?= $product->id; ?>" min="1" type="text" style="max-width: 4rem;">
                  <div class="input-group-text"><?= $product->unit_symbol; ?></div>
                </div>
              </div>
            </td>

            <td class="align-middle text-end">
              <span id="precio_venta-<?= $product->id; ?>">$<?= number_format($product->sale_without_iva, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
            </td>

            <td class="text-end align-middle">
              <span id="sale-price-label-<?= $product->id; ?>">$<?= number_format($product->sale_amount, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $product->id; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $product->id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
                <i class="fa fa-times-circle text-danger"></i>
              </a>
            </td>
          </tr>

          <?php $table_row_number++; ?>
        <?php endforeach; ?>

        <tr>
          <td colspan="6" class="p-0">
            <div class="d-flex border-top border-bottom w-100"></div>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="h5">Subtotal:</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-subtotal" class="text-dark">
              $<?= number_format($cart_data->sale_subtotal, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="h5">IVA:</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-iva" class="text-dark">
              $<?= number_format($cart_data->sale_iva, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="h5">Redondeo:</b>
          </td>

          <td class="text-end align-middle">
            <div class="form-group m-0 ms-auto" style="max-width: 5rem;">
              <input id="carrito-redondeo" class="form-control form-control-sm" value="<?= $cart_data->sale_rounding; ?>" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" type="number">
            </div>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="h4 fw-bold text-success">Total:</b>
          </td>

          <td class="text-end align-middle">
            <u id="carrito-total" class="text-success fw-bold">
              $<?= number_format($cart_data->sale_total, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </u>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    initNumberInput();
  </script>
<?php endif; ?>