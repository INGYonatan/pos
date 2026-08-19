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
          <th>Producto</th>
          <th>P. Venta</th>
          <th style="width: 10rem;">Cantidad</th>
          <th class="text-end">P. Neto</th>
          <th class="text-end">Existencia</th>
          <th class="text-end">Descuento</th>
          <th class="text-end" style="width: 10rem;">Importe</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($list as $key => $product) : ?>
          <tr class="pos-tr">
            <td class="align-middle">
              <?= $product->name; ?>
            </td>

            <td class="align-middle">
              <div id="popover-discount-<?= $product->id; ?>" class="custom-popover">
                <a class="fw-bold text-dark" data-toggle="custom-popover" data-popover="#popover-discount-<?= $product->id; ?>" href="javascript:void(0)">
                  <span id="precio_venta-<?= $product->id; ?>">$<?= number_format($product->sale_without_iva, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
                </a>

                <form id="popover-discount-form-<?= $product->id; ?>" class="custom-popover-container discount-form form-validate" autocomplete="off">
                  <div class="row">
                    <div class="col-12">
                      <table class="table table-sm mt-2">
                        <tbody>
                          <tr>
                            <td class="align-middle">
                              <label class="form-label m-0">Precio normal</label>
                            </td>

                            <td class="align-middle">
                              <h5 class="text-heading">$<?= number_format($product->origin_sale_price - $product->sale_iva, DECIMALS_CURRENCY); ?></h5>
                              <input id="precio_venta_in-<?= $product->id; ?>" name="price" value="<?= ($product->origin_sale_price - $product->sale_iva); ?>" type="hidden">
                            </td>
                          </tr>

                          <tr>
                            <td class="align-middle">
                              <label class="form-label m-0">Nuevo precio($)</label>
                            </td>

                            <td class="align-middle">
                              <input id="precio_venta_mod-<?= $product->id; ?>" class="form-control form-control-sm" name="new_price" value="<?= $product->sale_without_iva;; ?>" type="text" required>
                            </td>
                          </tr>

                          <tr>
                            <td class="align-middle">
                              <label class="form-label m-0">Descuento(%)</label>
                            </td>

                            <td class="align-middle">
                              <input class="form-control form-control-sm" name="discount" value="<?= $product->discount; ?>" type="text" required>
                            </td>
                          </tr>

                          <tr class="price-with-discount"></tr>

                          <input name="uid" value="<?= $product->id; ?>" type="hidden">
                          <input name="itemId" value="<?= $product->id; ?>" type="hidden">
                          <input name="action" value="update-discount-<?= $identifier; ?>" type="hidden">
                          <input name="place" value="<?= $identifier; ?>" type="hidden">

                          <tr>
                            <td colspan="2">
                              <div class="d-flex align-items-center justify-content-center gap-1">
                                <button class="btn btn-primary btn-sm" type="submit">
                                  <i class="fa fa-check-circle"></i> Cambiar precio
                                </button>

                                <button data-toggle="close-custom-popover" class="btn btn-danger btn-sm" type="button">
                                  <i class="fa fa-times-circle"></i> Cancelar
                                </button>
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </form>
              </div>
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
              <span id="precio_neto-<?= $product->id; ?>">$<?= number_format($product->origin_sale_price, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
            </td>

            <td class="align-middle text-end">
              <?= format_decimal_number($product->stock, DECIMALS_CURRENCY); ?>
            </td>

            <td class="align-middle text-end">
              <?= format_decimal_number($product->discount, DECIMALS_CURRENCY); ?>%
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
          <td colspan="7" class="p-0">
            <div class="d-flex border-top border-bottom w-100"></div>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="6">
            <b class="h5">Subtotal:</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-subtotal" class="text-dark">
              $<?= number_format($cart_data->sale_subtotal, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="6">
            <b class="h5">IVA:</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-iva" class="text-dark">
              $<?= number_format($cart_data->sale_iva, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="6">
            <b class="h5">Redondeo:</b>
          </td>

          <td class="text-end align-middle">
            <div class="form-group m-0 ms-auto" style="max-width: 5rem;">
              <input id="carrito-redondeo" class="form-control form-control-sm" value="<?= $cart_data->sale_rounding; ?>" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" type="number">
            </div>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="6">
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

    $('[data-bs-toggle="tooltip"]').tooltip();

    $('.discount-form').each(function() {
      const formId = $(this).attr('id');

      $(`#${formId} [name=discount]`).on('keyup', () => {
        const priceData = calculatePriceWithPercentajeDiscount({
          price: $(`#${formId} [name=price]`).val(),
          newPrice: $(`#${formId} [name=new_price]`).val(),
          discount: $(`#${formId} [name=discount]`).val()
        });

        if (!priceData) return;

        $(`#${formId} [name=price]`).val(priceData.price);
        $(`#${formId} [name=new_price]`).val(priceData.newPrice);
        $(`#${formId} [name=discount]`).val(priceData.discount);
      });

      $(`#${formId} [name=new_price]`).on('keyup', () => {
        const priceData = calculatePriceWithNewPrice({
          price: $(`#${formId} [name=price]`).val(),
          newPrice: $(`#${formId} [name=new_price]`).val(),
          discount: $(`#${formId} [name=discount]`).val()
        });

        if (!priceData) return;

        $(`#${formId} [name=price]`).val(priceData.price);
        $(`#${formId} [name=new_price]`).val(priceData.newPrice);
        $(`#${formId} [name=discount]`).val(priceData.discount);
      });
    });

    $init_form_validates();
  </script>
<?php endif; ?>