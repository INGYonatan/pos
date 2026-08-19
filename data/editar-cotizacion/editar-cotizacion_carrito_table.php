<?php $counter = 1; ?>

<?php if (isEmptyArray($list)) : ?>
  <div class="p-2">
    El carrito está vacío
  </div>
<?php endif; ?>

<?php if (!isEmptyArray($list)) : ?>
  <table class="table table-hover table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Producto</th>
        <th class="text-center">Tipo</th>
        <th class="text-center">Stock</th>
        <th class="text-center quantity-col">Cant.</th>
        <th class="text-end quantity-col">Precio</th>
        <th class="text-center">Descuento</th>
        <th class="text-end">Importe</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($list as $product) :
        $id       = $product->id;
        $name     = $product->name;
        $type     = $product->type;
        $stock    = $product->stock;
        $quantity = $product->cart_quantity;

        $prices   = [$product->sale_price1, $product->sale_price2, $product->sale_price3];
        $price    = $product->cart_sale_price_with_iva;
        $iva      = $product->cart_sale_iva;
        $discount = $product->cart_sale_discount;
        $netPrice = $product->cart_sale_net_price;
        $amount   = $netPrice * $quantity;
        $comment = $product->comments ?? '';
      ?>
        <tr class="pos-tr">
          <td>
            <?= $counter++; ?>
          </td>

          <td>
            <?= $name; ?>
            <span class="<?= $identifier ?>-comments-popup" data-id="<?= $id; ?>" data-comment="<?= $comment ?? ''; ?>"></span>
          </td>

          <td class="text-center">
            <span class="badge bg-primary"><?= $type; ?></span>
          </td>

          <td class="text-center">
            <?php if ($stock == 0) : ?>
              <span class="badge btn-light text-danger">Sin stock</span>
            <?php endif; ?>

            <?php if ($stock > 0) : ?>
              <span class="badge btn-light text-success">
                <?= format_decimal_number($stock, DECIMALS_CURRENCY_TICKET); ?>
              </span>
            <?php endif; ?>
          </td>

          <td class="text-center">
            <form id="quantity-<?= $id; ?>-form" class="d-flex align-items-center gap-1 quantity-form" autocomplete="off">
              <div class="form-group form-group-sm m-0">
                <div class="input-group">
                  <input class="form-control form-control-sm" name="quantity" min="1" value="<?= $quantity; ?>" type="number">

                  <input name="itemId" value="<?= $id; ?>" type="hidden">

                  <button class="btn btn-sm px-1 btn-success pulse" title="Actualizar cantidad" type="submit">
                    <i class="fa fa-sync"></i>
                  </button>
                </div>
              </div>
            </form>
          </td>

          <td class="text-end">
            <form id="form-update-price-<?= $id; ?>" class="form-group form-group-sm m-0 cs-datalist update-price-form" autocomplete="off">
              <input name="itemId" value="<?= $id; ?>" type="hidden">

              <div class="input-group">
                <input id="price-<?= $id; ?>" class="form-control form-control-sm cs-datalist-price" name="salePrice" list="prices-<?= $id; ?>" value="<?= round($price, DECIMALS_CURRENCY_TICKET); ?>" style="text-align: right;">

                <button class="btn btn-sm px-1 btn-success pulse" title="Actualizar precio" type="submit">
                  <i class="fa fa-sync"></i>
                </button>
              </div>

              <ul class="cs-datalist-options">
                <?php foreach ($prices as $rPrice) :
                  if (!$rPrice) continue;
                  if ($rPrice <= 0) continue;
                ?>
                  <li class="cs-datalist-option" data-value="<?= $rPrice; ?>">
                    $<?= number_format($rPrice, DECIMALS_CURRENCY_TICKET); ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            </form>
          </td>

          <td class="text-center">
            <a class="fw-bold text-dark btn-discount" data-bs-toggle="modal" data-bs-target="#modal-discount" data-itemId="<?= $id; ?>" data-branchId="<?= $branch_id; ?>" data-price="<?= $price; ?>" data-netPrice="<?= $netPrice; ?>" data-discount="<?= $discount; ?>" data-discountLimit="<?= $discount_limit; ?>" href="javascript:void(0)">
              <span id="discount-<?= $id; ?>"><?= format_decimal_number($discount, DECIMALS_CURRENCY_TICKET); ?></span>%
            </a>
          </td>

          <td class="text-end">
            $<span id="netPrice-<?= $id; ?>"><?= number_format($amount, DECIMALS_CURRENCY_TICKET); ?></span>
          </td>

          <td id="cart-actions-<?= $identifier; ?>-<?= $id; ?>" class="pos-actions-td">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

            <a class="pos-btn btn-remove-item" data-itemId="<?= $id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
              <i class="fa fa-times-circle text-danger"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="row">
    <div class="col-12 col-lg-6 col-xl-4 ms-auto">
      <table class="table table-borderless">
        <tbody>
          <tr>
            <td class="text-end align-middle">
              <b class="h4">Subtotal:</b>
            </td>

            <td class="text-end align-middle">
              <span class="text-dark">
                $<span id="cartSubtotal"><?= number_format($cart_session->sale_subtotal, DECIMALS_CURRENCY_TICKET); ?></span> <?= $mxn_symbol; ?>
              </span>
            </td>
          </tr>

          <tr>
            <td class="text-end align-middle">
              <b class="h4">IEPS:</b>
            </td>

            <td class="text-end align-middle">
              <span class="text-dark">
                $<span id="cartIEPS"><?= number_format($cart_session->sale_ieps ?? 0, DECIMALS_CURRENCY_TICKET); ?></span> <?= $mxn_symbol; ?>
              </span>
            </td>
          </tr>

          <tr>
            <td class="text-end align-middle">
              <b class="h4">IVA:</b>
            </td>

            <td class="text-end align-middle">
              <span class="text-dark">
                $<span id="cartIVA"><?= number_format($cart_session->sale_iva, DECIMALS_CURRENCY_TICKET); ?></span> <?= $mxn_symbol; ?>
              </span>
            </td>
          </tr>

          <tr>
            <td class="text-end align-middle">
              <b class="h4">Redondeo:</b>
            </td>

            <td class="text-end align-middle">
              <div class="form-group m-0 ms-auto" style="max-width: 5rem;">
                <input id="cartRounding" class="form-control form-control-sm" value="<?= $cart_session->sale_rounding; ?>" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" type="number">
              </div>
            </td>
          </tr>

          <tr>
            <td class="text-end align-middle">
              <b class="h3 fw-bold text-success">Total:</b>
            </td>

            <td class="text-end align-middle">
              <u class="text-success fw-bold">
                $<span id="cartTotal"><?= number_format($cart_session->sale_total, DECIMALS_CURRENCY_TICKET); ?></span> <?= $mxn_symbol; ?>
                <input id="atc-cartTotal" value="<?= $cart_session->sale_total; ?>" type="hidden">
              </u>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    initNumberInput();

    $('[data-bs-toggle="tooltip"]').tooltip();
    $('.quantity-form').on('submit', $cart_updateItemQuantity);
    $('.btn-discount').on('click', $cart_initDiscountformProps);
    $('#cartRounding').on('keyup change', $cart_updateRounding);
    $('.btn-remove-item').on('click', $cart_removeItem);
  </script>
<?php endif; ?>