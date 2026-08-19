<?php $colspan = 8; ?>

<?php if (isEmptyArray($list)) : ?>
  <div class="p-2">
    El carrito está vacío
  </div>

  <script>
    $('#atc-payWith').val('0');
    $('#atc-exchange').val('0');
  </script>
<?php endif; ?>

<?php if (!isEmptyArray($list)) : ?>
  <table class="table table-sm table-hover">
    <thead>
      <tr class="table-dark">
        <th>Producto</th>
        <th class="text-center">Tipo</th>
        <th class="text-center">Existencia</th>
        <th class="quantity-col">Cantidad</th>
        <th class="text-end">Precio</th>
        <th class="text-end">IVA</th>
        <th class="text-end">Descuento</th>
        <th class="text-end">Precio Neto</th>
        <th class="text-end">Importe (Sin IVA)</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($list as $product) :
        $id             = $product->id;
        $name           = $product->name;
        $stock          = $product->stock;
        $discount_limit = $product->discount_limit;
        $quantity       = $product->cart_quantity;
        $price          = $product->cart_sale_price;
        $iva            = $product->cart_sale_iva;
        $price_with_iva = $product->cart_sale_price_with_iva;
        $discount       = $product->cart_sale_discount;
        $net_price      = $product->cart_sale_net_price;
        $amount         = $product->cart_sale_amount_without_iva;
        $unit_symbol    = $product->unit_symbol;
        $type           = $product->type;
        $serial_numbers = $product->serial_numbers
      ?>
        <tr class="pos-tr">
          <td>
            <?= $name; ?>
          </td>

          <td class="text-center">
            <?php
            $badge_color = 'bg-secondary';
            $show_serial = false;

            if ($type === 'equipo') {
              $badge_color = 'bg-danger';
              $show_serial = true; // Solo equipo maneja números de serie
            } elseif ($type === 'llantas') {
              $badge_color = 'bg-primary';
            } elseif ($type === 'rines') {
              $badge_color = 'bg-info';
            } elseif ($type === 'refacciones') {
              $badge_color = 'bg-warning';
            } elseif ($type === 'servicios') {
              $badge_color = 'bg-success';
            } elseif ($type === 'otros') {
              $badge_color = 'bg-secondary';
            }
            ?>

            <span class="badge <?= $badge_color; ?> text-capitalize"><?= $type; ?></span>

            <?php if ($show_serial) : ?>
              <br>
              <a class="btn-serial-numbers btn btn-xs btn-light border rounded text-dark" data-bs-toggle="modal" data-serialNumbers="<?= htmlentities(json_encode($serial_numbers)); ?>" data-itemId="<?= $id; ?>" data-branchId="<?= $branch_id; ?>" href="#modal-serialNumbers">Nros. serie</a>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <?php if ($stock > 0) : ?>
              <span class="badge btn-light text-success">
                <?= format_decimal_number($stock, DECIMALS_CURRENCY); ?>
              </span>
            <?php endif; ?>

            <?php if ($stock == 0) : ?>
              <span class="badge btn-light text-danger">Sin stock</span>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <form id="quantity-<?= $id; ?>-form" class="d-flex align-items-center gap-1 quantity-form">
              <div class="form-group form-group-sm m-0">
                <input class="form-control form-control-sm" name="quantity" min="1" max="<?= $stock; ?>" value="<?= $quantity; ?>" type="number">
              </div>

              <input name="itemId" value="<?= $id; ?>" type="hidden">

              <button class="btn btn-sm px-1 btn-success" title="Actualizar cantidad" type="submit">
                <i class="fa fa-check-circle"></i>
              </button>
            </form>
          </td>

          <td class="text-end">
            $<span id="price-<?= $id; ?>"><?= number_format($price, DECIMALS_CURRENCY); ?></span>
          </td>

          <td class="text-end">
            $<span id="iva-<?= $id; ?>"><?= number_format($iva, DECIMALS_CURRENCY); ?></span>
          </td>

          <td class="text-end">
            <a class="fw-bold text-dark btn-discount" data-bs-toggle="modal" data-bs-target="#modal-discount" data-itemId="<?= $id; ?>" data-branchId="<?= $branch_id; ?>" data-price="<?= $price_with_iva; ?>" data-netPrice="<?= $net_price; ?>" data-discount="<?= $discount; ?>" data-discountLimit="<?= $discount_limit; ?>" href="javascript:void(0)">
              <span id="discount-<?= $id; ?>"><?= format_decimal_number($discount, DECIMALS_CURRENCY); ?></span>%
            </a>
          </td>

          <td class="text-end">
            $<span id="netPrice-<?= $id; ?>"><?= number_format($net_price, DECIMALS_CURRENCY); ?></span>
          </td>

          <td class="text-end">
            $<span id="amount-<?= $id; ?>"><?= number_format($amount, DECIMALS_CURRENCY); ?></span>
          </td>

          <td id="cart-actions-<?= $identifier; ?>-<?= $id; ?>" class="pos-actions-td">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

            <a class="pos-btn btn-remove-item" data-itemId="<?= $id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
              <i class="fa fa-times-circle text-danger"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>

      <tr class="border-top">
        <td class="text-end align-middle" colspan="<?= $colspan; ?>">
          <b class="h5">Subtotal:</b>
        </td>

        <td class="text-end align-middle">
          <span class="text-dark">
            $<span id="cartSubtotal"><?= number_format($cart_session->sale_subtotal, DECIMALS_CURRENCY); ?></span> <?= $mxn_symbol; ?>
          </span>
        </td>
      </tr>

      <tr class="border-top">
        <td class="text-end align-middle" colspan="<?= $colspan; ?>">
          <b class="h5">IVA:</b>
        </td>

        <td class="text-end align-middle">
          <span class="text-dark">
            $<span id="cartIVA"><?= number_format($cart_session->sale_iva, DECIMALS_CURRENCY); ?></span> <?= $mxn_symbol; ?>
          </span>
        </td>
      </tr>

      <tr class="border-top">
        <td class="text-end align-middle" colspan="<?= $colspan; ?>">
          <b class="h5">Redondeo:</b>
        </td>

        <td class="text-end align-middle">
          <div class="form-group m-0 ms-auto" style="max-width: 5rem;">
            <input id="cartRounding" class="form-control form-control-sm" value="<?= $cart_session->sale_rounding; ?>" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" type="number">
          </div>
        </td>
      </tr>

      <tr class="border-top">
        <td class="text-end align-middle" colspan="<?= $colspan; ?>">
          <b class="h4 fw-bold text-success">Total:</b>
        </td>

        <td class="text-end align-middle">
          <u class="text-success fw-bold">
            $<span id="cartTotal"><?= number_format($cart_session->sale_total, DECIMALS_CURRENCY); ?></span> <?= $mxn_symbol; ?>
            <input id="atc-cartTotal" value="<?= $cart_session->sale_total; ?>" type="hidden">
          </u>
        </td>
      </tr>
    </tbody>
  </table>

  <script>
    initNumberInput();

    $('[data-bs-toggle="tooltip"]').tooltip();
    $('.quantity-form').on('submit', $cart_updateItemQuantity);
    $('.btn-discount').on('click', $cart_initDiscountformProps);
    $('#cartRounding').on('keyup change', $cart_updateRounding);
    $('.btn-remove-item').on('click', $cart_removeItem);

    $('#atc-payWith').val(strToNumber('<?= $cart_session->sale_total; ?>'));
    $('#atc-exchange').val('0');

    $('.btn-serial-numbers').on('click', function() {
      const serialNumbers = JSON.parse($(this).attr('data-serialNumbers'));
      const itemId = $(this).attr('data-itemId');
      const branchId = $(this).attr('data-branchId');

      storeCart._createSerialNumberInputs(serialNumbers);

      $('#<?= $identifier; ?>-branchId').val(branchId);
      $('#<?= $identifier; ?>-itemId').val(itemId);
    });
  </script>
<?php endif; ?>