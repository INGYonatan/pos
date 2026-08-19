<?php

/**
 * @var array $cartSession
 */
$cartSession  = $_SESSION[$cartSSID] ?? [];
$isEmptyList  = isEmptyArray($cartSession);
$pageId       = "inventario-solicitudes-de-traspasos";
?>

<?php if ($isEmptyList) : ?>
  <div class="row">
    <div class="col-12">
      <div class="alert alert-info">
        <i class="fas fa-exclamation-triangle"></i> No hay productos en el carrito
      </div>
    </div>
  </div>
<?php endif; ?>

<?php if (!$isEmptyList) : ?>
  <table class="table table-hover">
    <thead>
      <tr>
        <th>Producto</th>
        <th class="text-center">Stock<br>Propio</th>
        <th class="text-center">Stock<br>Disponible</th>
        <th class="text-center">Cantidad solicitada</th>
        <th class="text-center">Stock<br>Propio final</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($cartSession as $product) :
        $productId            = $product['id_producto'];
        $productName          = $product['nombre_producto'];
        $productCode          = $product['codigo'];
        $requestedQuantity    = $product['cantidad_solicitada'];
        $unitSymbol           = $product['unidad'] === 'A granel' ? 'kg.' : '';
        $stockOrigin          = $product['stock'];  # Stock en la sucursal origen (límite máximo)
        $stockDestination     = $product['stock_destino'] ?? 0;  # Stock en la sucursal destino
        $stockDestinationFinal  = $stockDestination + $requestedQuantity;
      ?>
        <tr class="pos-tr">
          <td class="align-middle">
            <span class="fw-bold"><?= $productName; ?></span>
            <br>
            <small class="text-muted"><?= $productCode; ?></small>
          </td>

          <td class="align-middle text-center">
            <?php if ($stockDestination > 0) : ?>
              <span class="badge btn-light text-success">
                <?= format_decimal_number($stockDestination, DECIMALS_CURRENCY); ?>
              </span>
            <?php endif; ?>

            <?php if ($stockDestination == 0) : ?>
              <span class="badge btn-light text-danger">Sin stock</span>
            <?php endif; ?>
            <?= $unitSymbol; ?>
          </td>

          <td class="align-middle text-center">
            <?= format_decimal_number($stockOrigin, DECIMALS_CURRENCY); ?> <?= $unitSymbol; ?>
          </td>

          <td class="align-middle text-center">
            <form id="quantity-<?= $productId; ?>-form" class="d-flex align-items-center gap-1 quantity-form justify-content-center">
              <div class="form-group m-0">
                <div class="input-group input-group-sm">
                  <input class="form-control" name="quantity" min="1" max="<?= $stockOrigin; ?>" value="<?= $requestedQuantity; ?>" type="number" style="width: 6rem">

                  <input name="itemId" value="<?= $productId; ?>" type="hidden">

                  <button class="input-group-text btn-success pulse" title="Actualizar cantidad" type="submit">
                    <i class="fa fa-sync"></i>
                  </button>
                </div>
              </div>
            </form>
          </td>

          <td class="align-middle text-center">
            <?= format_decimal_number($stockDestinationFinal, DECIMALS_CURRENCY); ?> <?= $unitSymbol; ?>
          </td>

          <td id="cart-actions-<?= $pageId; ?>-<?= $productId; ?>" class="pos-actions-td">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

            <a class="pos-btn btn-remove-item" data-itemId="<?= $productId; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
              <i class="fa fa-times-circle text-danger"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<script>
  initNumberInput();

  $('[data-bs-toggle="tooltip"]').tooltip();
  $('.quantity-form').on('submit', $cart_updateItemQuantity);
  $('.btn-remove-item').on('click', $cart_removeItem);
</script>