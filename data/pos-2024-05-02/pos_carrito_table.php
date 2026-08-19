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

          <th class="text-end">Precio Neto</th>

          <?php if ($IS_MATRIZ) : ?>
            <th class="text-end" style="width: 15%;">Existencia</th>
            <th class="text-end" style="width: 15%;">Descuento</th>
          <?php endif; ?>
        </tr>
      </thead>

      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $producto_total = $row['cantidad'] * $row['precio_venta'];

          if ($row['unidad'] === $TIPO_A_GRANEL) $producto_total = parsePricePerBulk($producto_total);

          $carrito_total  = $carrito_total + $producto_total;
          $unidad_qt      = $row['unidad'] == 'A granel' ? 'kg' : 'pza';
        ?>
          <tr class="pos-tr">
            <td>
              <?= $row['nombre_producto']; ?>
            </td>

            <td>
              <?php if (!$IS_MATRIZ) : ?>
                $<?= number_format($row['precio_venta'], DECIMALS_CURRENCY); ?>
              <?php endif; ?>

              <?php if ($IS_MATRIZ) : ?>
                <div id="popover-discount-<?= $row['id_producto'] ?>" class="custom-popover">
                  <a class="fw-bold text-dark" data-toggle="custom-popover" data-popover="#popover-discount-<?= $row['id_producto'] ?>" href="javascript:void(0)">
                    $<?= number_format($row['precio_venta'], DECIMALS_CURRENCY); ?>
                  </a>

                  <form id="popover-discount-form-<?= $row['id_producto'] ?>" class="custom-popover-container discount-form form-validate" autocomplete="off">
                    <div class="row">
                      <div class="col-12">
                        <table class="table table-sm mt-2">
                          <tbody>
                            <tr>
                              <td class="align-middle">
                                <label class="form-label m-0">Precio normal</label>
                              </td>

                              <td class="align-middle">
                                <h5 class="text-heading">$<?= number_format($row['precio_original'], DECIMALS_CURRENCY); ?></h5>
                                <input name="price" value="<?= $row['precio_original']; ?>" type="hidden">
                              </td>
                            </tr>

                            <tr>
                              <td class="align-middle">
                                <label class="form-label m-0">Nuevo precio($)</label>
                              </td>

                              <td class="align-middle">
                                <input class="form-control form-control-sm" name="new_price" value="<?= $row['precio_venta']; ?>" type="text" required>
                              </td>
                            </tr>

                            <tr>
                              <td class="align-middle">
                                <label class="form-label m-0">Descuento(%)</label>
                              </td>

                              <td class="align-middle">
                                <input class="form-control form-control-sm" name="discount" value="<?= $row['descuento']; ?>" type="text" required>
                              </td>
                            </tr>

                            <tr class="price-with-discount"></tr>

                            <input name="uid" value="<?= $row['id_producto']; ?>" type="hidden">
                            <input name="action" value="add-discount-pos" type="hidden">
                            <input name="place" value="pos" type="hidden">

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
              <?php endif; ?>
            </td>

            <td>
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-sm decimal-input quantity-input" name="cantidad" value="<?= $row['cantidad']; ?>" data-stock="<?= $row['stock']; ?>" data-itemId="<?= $row['id_producto']; ?>" min="1" type="text">

                  <div class="input-group-text">
                    <?= $unidad_qt; ?>
                  </div>
                </div>
              </div>
            </td>

            <td class="text-right">
              <span id="total-<?= $row['id_producto']; ?>">$<?= number_format($producto_total, DECIMALS_CURRENCY) ?></span>
            </td>

            <?php if ($IS_MATRIZ) : ?>
              <td class="text-end"><?= format_decimal_number($row['stock'], DECIMALS_CURRENCY); ?></td>
              <td class="text-end"><?= format_decimal_number($row['descuento'], DECIMALS_CURRENCY); ?>%</td>
            <?php endif; ?>

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
