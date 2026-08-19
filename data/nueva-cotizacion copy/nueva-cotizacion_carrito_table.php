<?php
$table_row_number = 1;
$subtotal         = 0;
$total_iva        = 0;
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
          <th class="text-end" style="width: 10rem;">P. Venta</th>
          <th class="text-end">Importe (Sin IVA)</th>
          <!-- <th class="text-right" style="width: 10rem;"></th> -->
        </tr>
      </thead>
      <tbody>
        <?php foreach ($carrito as $key => $row) :
          $data_producto  = parseCartProduct($row, 'precio_venta');

          $id_producto    = $data_producto->id_producto;
          $codigo         = $data_producto->codigo;
          $producto       = $data_producto->producto;
          $cantidad       = $data_producto->cantidad;
          $aplica_iva     = $data_producto->aplica_iva;
          $precio         = $data_producto->precio;
          $importe        = $data_producto->importe;
          $unidad         = $data_producto->unidad;
          $iva            = $data_producto->iva;

          $subtotal       = $subtotal + $importe;
          $total_iva      = $total_iva + $iva;
        ?>
          <tr class="pos-tr">
            <th class="align-middle" scope="row">
              <?= $table_row_number; ?>
            </th>

            <td class="align-middle">
              <?= $codigo; ?>
            </td>

            <td class="align-middle">
              <?= $producto; ?>
            </td>

            <td class="align-middle">
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input class="form-control form-control-sm decimal-input quantity-input" name="cantidad" value="<?= $cantidad; ?>" data-stock="<?= $row['stock']; ?>" data-itemId="<?= $id_producto; ?>" min="1" type="text">
                  <div class="input-group-text"><?= $unidad; ?></div>
                </div>
              </div>
            </td>

            <td class="align-middle text-end">
              <?php
              /* 
              <div class="form-group mb-0">
                <div class="input-group input-group-sm">
                  <input id="precio_venta-<?= $row['id_producto']; ?>" class="form-control form-control-sm decimal-input price-input" name="precio_venta" value="<?= $row['precio_venta']; ?>" data-itemId="<?= $row['id_producto']; ?>" type="text">
                  <div class="input-group-text">$</div>
                </div>
              </div>
              */
              ?>

              <span id="precio_venta-<?= $id_producto; ?>">$<?= number_format($precio, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
            </td>

            <td class="text-end align-middle">
              <span id="sale-price-label-<?= $id_producto; ?>">$<?= number_format($importe, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?></span>
            </td>

            <td id="cart-actions-<?= $identifier; ?>-<?= $id_producto; ?>" class="pos-actions-td">
              <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

              <a class="pos-btn btn-remove-item" data-itemId="<?= $id_producto; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
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
            <b class="text-black">SUBTOTAL</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-subtotal" class="text-dark">
              $<?= number_format($subtotal, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="text-black">IVA</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-iva" class="text-dark">
              $<?= number_format($total_iva, DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>

        <tr class="border-top">
          <td class="text-end align-middle" colspan="5">
            <b class="text-black">TOTAL</b>
          </td>

          <td class="text-end align-middle">
            <span id="carrito-total" class="text-dark">
              $<?= number_format(($subtotal + $total_iva), DECIMALS_CURRENCY); ?> <?= $mxn_symbol; ?>
            </span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <script>
    initNumberInput();
  </script>
<?php endif; ?>