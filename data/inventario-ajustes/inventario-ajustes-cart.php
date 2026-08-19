<?php

/**
 * @var ShoppingCart $list
 */
$list         = $cartSession->list;
$isEmptyList  = isEmptyArray($list);
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
        <th class="text-center">Tipo</th>
        <th class="text-center">Stock inicial</th>
        <th class="text-center">Cantidad</th>
        <th class="text-center">Stock final</th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ($list as $product) :
        $id             = $product->id;
        $name           = $product->name;
        $stock          = $product->stock;
        $quantity       = $product->cart_quantity;
        $unitSymbol     = $product->unit_symbol;
        $type           = $product->type;
        $serialNumbers  = $product->serial_numbers;
        $requiresSerialNumber = $product->requires_serial_number;

        $newStock       = $stock + $quantity;

        if ($adjustment == TIPO_MOVIMIENTO_DECREMENTO) $newStock = $stock - $quantity;

        $serialNumberCatalog = getProductSerialNumbers($id, $branchId);
      ?>
        <tr class="pos-tr">
          <td class="align-middle">
            <?= $name; ?>
          </td>

          <td class="align-middle text-center">
            <?php if ($requiresSerialNumber) : ?>
              <span class="badge bg-danger text-capitalize"><?= $type; ?></span>
              <br>
              <a
                class="btn-serial-numbers btn btn-xs btn-light border rounded text-dark"
                data-bs-toggle="modal"
                data-serialNumbers="<?= htmlentities(json_encode($serialNumbers)); ?>"
                data-serialNumberCatalog="<?= htmlentities(json_encode($serialNumberCatalog)); ?>"
                data-itemId="<?= $id; ?>"
                data-branchId="<?= $branchId; ?>"
                data-adjustment="<?= $adjustment; ?>"
                href="#modal-serialNumbers">Nros. serie</a>
            <?php endif; ?>

            <?php if (!$requiresSerialNumber) : ?>
              <span class="badge bg-primary text-capitalize"><?= $type; ?></span>
            <?php endif; ?>
          </td>

          <td class="align-middle text-center">
            <?php if ($stock > 0) : ?>
              <span class="badge btn-light text-success">
                <?= format_decimal_number($stock, DECIMALS_CURRENCY); ?>
              </span>
            <?php endif; ?>

            <?php if ($stock == 0) : ?>
              <span class="badge btn-light text-danger">Sin stock</span>
            <?php endif; ?>
          </td>

          <td class="align-middle text-center">
            <form id="quantity-<?= $id; ?>-form" class="d-flex align-items-center gap-1 quantity-form justify-content-center">
              <div class="form-group m-0">
                <div class="input-group input-group-sm">
                  <input class="form-control" name="quantity" min="1" value="<?= $quantity; ?>" type="number" style="width: 5rem">

                  <input name="itemId" value="<?= $id; ?>" type="hidden">

                  <button class="input-group-text btn-success pulse" title="Actualizar cantidad" type="submit">
                    <i class="fa fa-sync"></i>
                  </button>
                </div>
              </div>
            </form>
          </td>

          <td class="align-middle text-center">
            <?php if ($newStock > 0) : ?>
              <span class="badge btn-light text-success">
                <?= format_decimal_number($newStock, DECIMALS_CURRENCY); ?>
              </span>
            <?php endif; ?>

            <?php if ($newStock == 0) : ?>
              <span class="badge btn-light text-danger">Sin stock</span>
            <?php endif; ?>
          </td>

          <td id="cart-actions-<?= $pageId; ?>-<?= $id; ?>" class="pos-actions-td">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>

            <a class="pos-btn btn-remove-item" data-itemId="<?= $id; ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar producto" href="javascript:void(0)">
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

  $('.btn-serial-numbers').on('click', function() {
    const serialNumbers = JSON.parse($(this).attr('data-serialNumbers'));
    const itemId = $(this).attr('data-itemId');
    const adjustment = $(this).attr('data-adjustment');
    const serialNumberCatalog = JSON.parse($(this).attr('data-serialNumberCatalog'));

    const branchId = $(this).attr('data-branchId');

    if (adjustment != "decremento") storeCart._createSerialNumberInputs(serialNumbers);
    if (adjustment == "decremento") storeCart._createSerialNumberInputs(serialNumbers, serialNumberCatalog);

    $('#<?= $pageId; ?>-branchId').val(branchId);
    $('#<?= $pageId; ?>-itemId').val(itemId);
    $('#<?= $pageId; ?>-adjustment').val(adjustment);
    adjustment
  });
</script>