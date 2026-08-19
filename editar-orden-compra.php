<?php
include 'inc/session.inc.php';
require 'data/lib/helpers/purchase-orders.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$purchase_order_id = cleanStr($_GET['uid']);

if (!$purchase_order_id) :
  closeSession();
  die;
endif;

$purchase_order = purchase_order_get_by_id($purchase_order_id);
$cart           = purchase_order_create_cart_edit($purchase_order_id);
$carrito_ssid   = SESSION_CARRITO_EDITAR_ORDEN_COMPRA;

$_SESSION[$carrito_ssid] = $cart;

$page_config = [
  'page_title'        => 'Editar orden de compra',
  'page_identifier'   => 'editar-orden-compra'
];

$productos_page_config_id   = 'productos';
$proveedores_page_config_id = 'proveedores';

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">
</head>

<body class="loading">
  <!-- Begin page -->
  <div id="wrapper">
    <!-- HEADER -->
    <?php include 'src/components/header.php'; ?>

    <!-- SIDEBAR -->
    <?php include 'src/components/sidebar.php'; ?>

    <div class="content-page">
      <div class="content">
        <div class="container-fluid">
          <?php renderComponent("page-title", [
            "pageTitle"       => "Editar orden de compra",
            "pageDescription" => "Edita la orden de compra para tu sucursal"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-8 col-xl-8">
              <div class="card card-border-top border-top-primary">
                <form class="card-header bg-transparent">
                  <div class="row">
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-cantidad">Cantidad</label>
                        <input id="atc-cantidad" class="form-control form-control-lg decimal-input" min="0" value="1" type="text">
                      </div>
                    </div>

                    <div class="col-12 <?= checkModuleActionPermission('productos', 'agregar') ? 'col-md-4 col-lg-6 col-xl-8 ps-md-4' : 'col-md-8 col-lg-9 col-xl-10 ps-md-4' ?>">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-producto">Escribe el nombre del producto</label>

                        <div class="input-group">
                          <input id="atc-producto" class="form-control form-control-lg" type="text">

                          <button id="btn-carrito-modal-productos" class="btn btn-white bg-white btn-lg" for="atc-product" data-bs-toggle="modal" data-bs-target="#carrito-modal-productos-modal" type="button">
                            <i class="fa fa-search"></i>
                          </button>
                        </div>
                      </div>
                    </div>

                    <?php if (checkModuleActionPermission('productos', 'agregar')) : ?>
                      <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                        <div class="form-group">
                          <label class="form-label" for="">Producto</label>
                          <button id="btn-add-product" class="btn btn-lg btn-primary btn-block w-100" data-bs-toggle="modal" data-bs-target="#<?= $productos_page_config_id; ?>-modal" type="button">
                            <i class="fa fa-plus"></i> Nuevo
                          </button>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </form>

                <div id="tabla-carrito" class="card-body"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>

                <div class="card-footer text-end">
                  <button id="btn-clean-cart" class="btn btn-danger" type="button">
                    <i class="fa fa-trash"></i> Vacíar
                  </button>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-xl-4">
              <form id="<?= $page_config['page_identifier']; ?>-form-data" class="card" autocomplete="off">
                <div class="card-header bg-primary">
                  <h3 class="header-title text-dark m-0">Detalles de la orden de compra</h3>
                </div>

                <div class="card-body">
                  <div class="row" style="<?= $IS_ADMIN ? "" : "display: none;"; ?>">
                    <div class="col-12 col-md-8">
                      <div class="form-group">
                        <label class="form-label" for="id_sucursal">Sucursal<span class="text-danger">*</span></label>
                        <select id="id_sucursal" class="form-select form-select" required data-modalProductosBranchId>
                          <?= getBranchOfficesCatalog($purchase_order->branch_id, '--Seleccionar--', true, false, null, $IS_ADMIN ? false : true); ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="folio_documento">Folio del documento</label>
                        <input id="folio_documento" class="form-control" name="folio_documento" value="<?= $purchase_order->document_folio; ?>" type="text">
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="fecha_documento">Fecha del documento</label>
                        <div class="input-group">
                          <input id="fecha_documento" class="form-control datepicker" name="fecha_documento" value="<?= $purchase_order->document_date_format; ?>" type="text">

                          <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="metodo_pago">Método de pago<span class="text-danger">*</span></label>
                        <select id="metodo_pago" class="form-control form-select" name="metodo_pago" required>
                          <option <?= $purchase_order->payment_method === 'De contado' ? 'selected' : false; ?> value="De contado">De contado</option>
                          <option <?= $purchase_order->payment_method === 'Credito' ? 'selected' : false; ?> value="Credito">Credito</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="forma_pago">Forma de pago<span class="text-danger">*</span></label>
                        <select id="forma_pago" class="form-control form-select" name="forma_pago" required>
                          <option value="Efectivo" <?= $purchase_order->payment_form === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                          <option value="Cheque" <?= $purchase_order->payment_form === 'Cheque' ? 'selected' : ''; ?>>Cheque</option>
                          <option value="Transferencia" <?= $purchase_order->payment_form === 'Transferencia' ? 'selected' : ''; ?>>Transferencia</option>
                          <option value="Tarjeta de débito" <?= $purchase_order->payment_form === 'Tarjeta de débito' ? 'selected' : ''; ?>>Tarjeta de débito</option>
                          <option value="Tarjeta de crédito" <?= $purchase_order->payment_form === 'Tarjeta de crédito' ? 'selected' : ''; ?>>Tarjeta de crédito</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-9">
                      <div class="form-group">
                        <label class="form-label" for="id_proveedor">Proveedor<span class="text-danger">*</span></label>

                        <div class="input-group">
                          <select id="id_proveedor" class="form-control form-select supplier-catalog" name="id_proveedor" required>
                            <?= getSupplierCatalog($purchase_order->supplier_id); ?>
                          </select>

                          <button id="btn-add-supplier" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#<?= $proveedores_page_config_id; ?>-modal" type="button">
                            <i class="fa fa-plus"></i> Nuevo
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-laber" for="observaciones">Observaciones</label>
                        <textarea id="observaciones" class="form-control" name="observaciones" rows="3"><?= $purchase_order->observations; ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-footer bg-transparent border-0 py-0 text-end" style="display: none;">
                  <div class="d-flex w-100 flex-column align-items-center justify-content-center flex-md-row align-items-md-start justify-content-md-end gap-2 mt-2 mb-2">
                    <div class="text-right">
                      <h3 id="carrito-total" class="m-0">Total: $0.00 MXN</h3>
                    </div>
                  </div>
                </div>

                <div class="card-footer bg-transparent">
                  <div class="d-flex flex-column flex-lg-row align-items-center justify-content-center w-100 gap-2">
                    <button id="btn-save-changes" class="btn btn-block btn-secondary w-100" type="button">Guardar cambios</button>

                    <?php /* 
                    <!-- <button class="btn btn-block btn-primary w-100" type="submit">convertir a venta</button> -->
                    <!-- <button class="btn btn-block btn-primary w-100" type="submit">Realizar compra</button> -->
                    */ ?>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/productos.php'; ?>
      <?php include 'src/modals/proveedores.php'; ?>
      <?php include 'src/modals/carrito-numeros-serie.php'; ?>

      <?php
      $carrito_modal_productos_branch_id = $purchase_order->branch_id;
      include 'src/modals/carrito-modal-productos.php';
      ?>

      <!-- FOOTER -->
      <?php include 'src/components/footer.php'; ?>
    </div>
  </div>
  <!-- END wrapper -->

  <!-- PAGE LOADINGS -->
  <?php include 'src/components/page-loadings.php'; ?>

  <!-- REQUIRED SCRIPTS -->
  <?php include 'src/components/required-scripts.php'; ?>

  <!-- APP JS -->
  <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <!-- CART -->
  <script src="<?= BASE_URL; ?>/src/plugins/cart/main.js"></script>

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const getQuantity = () => $('#atc-cantidad').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      values: {
        id_orden_compra: <?= $purchase_order_id; ?>
      },
      dynamicValues: () => ({
        id_sucursal: $('#id_sucursal').val(),
        id_proveedor: $('#id_proveedor').val(),
        folio_documento: $('#folio_documento').val(),
        fecha_documento: $('#fecha_documento').val(),
        metodo_pago: $('#metodo_pago').val(),
        forma_pago: $('#forma_pago').val(),
        observaciones: $('#observaciones').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        $(`#cost-price-label-${response.id_producto}`).html(`${response.precio_costo_final}`);
        $(`#carrito-total`).html(`${response.total}`);
      },

      onSuccessUpdateItemPrice: response => {
        $(`#cost-price-label-${response.id_producto}`).html(`${response.precio_costo_final}`);
        $(`#carrito-total`).html(`${response.total}`);
      },

      onSuccessSaveCart: response => {
        //$(`#${PAGE_CONFIG.page_identifier}-form-data`).trigger('reset');
        if (response.ticket) window.open(response.ticket, '_blank');
        window.history.back();
      },
      onSuccessAdd: () => $('#atc-cantidad').val('1'),

      onSuccessCartDispatch: () => setTimeout(() => {
        window.history.back();
      }, 1000),

      onSuccess: response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        storeCart.loadCart();
      },

      onError: response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        storeCart.loadCart();
      }
    });

    const searchAutocomplete = new Autocomplete({
      identifier: 'atc-producto',
      source: `${BASE_URL}/data/autocompletes/productos.php`,
      minLength: 2,
      onSelect: product => storeCart.addItem(product.id_producto, getQuantity()),
      useCleanOnSelect: true
    });

    $(document).on('keyup', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemQuantity(itemId, quantity, false);
    });

    $(document).on('change', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemQuantity(itemId, quantity, false);
    });

    $(document).on('keyup', '.price-input', function(e) {
      e.stopPropagation();

      const price = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemPrice(itemId, price);
    });

    $(document).on('change', '.price-input', function(e) {
      e.stopPropagation();

      const price = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemPrice(itemId, price);
    });

    $(document).on('click', '.btn-remove-item', function(e) {
      e.stopPropagation();

      const itemId = $(this).attr('data-itemId');

      storeCart.removeItem(itemId);
    });

    $('#btn-clean-cart').on('click', () => storeCart.cleanCart());

    $(`#${PAGE_CONFIG.page_identifier}-form-data`).on('submit', e => {
      e.preventDefault();
      storeCart.saveCart();
    });

    $('#atc-tipo_ajuste').on('change', () => storeCart.loadCart());

    $('#atc-sucursal').on('change', function() {
      const idSucursal = $(this).val();

      $('#filter-id-sucursal').val(idSucursal);
      $('#filter-id-sucursal').closest('form').submit();
      storeCart.cartAction('change-branch-office');
    });

    $('#btn-add-product').on('click', () => {
      $(`#<?= $productos_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $productos_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $productos_page_config_id; ?>');
    });

    $('#btn-add-supplier').on('click', () => {
      $(`#<?= $proveedores_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $proveedores_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $proveedores_page_config_id; ?>');
    });

    $('#btn-save-changes').on('click', () => storeCart.cartDispatch('save-changes'));

    $(function() {
      searchAutocomplete.initAutocomplete();
      storeCart.loadCart();
      storeCart._initAddSerialNumber();
      storeCart._initProductsModal();
    });

    const updaterow = ({
      id,
      quantity,
      price,
      discount
    }) => {
      // Validar que los campos no estén vacíos
      if (quantity === '' || price === '' || discount === '') {
        return;
      }

      // Validar que la cantidad sea mayor a 0

      if (quantity == 0) return;

      callEndpoint({
        place: PAGE_CONFIG.page_identifier,
        parameters: {
          action: 'cart-update-row',
          id,
          quantity,
          price,
          discount
        },
      }).then(response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        if (response.status === "success") storeCart.loadCart();
      })
    }
  </script>
</body>

</html>