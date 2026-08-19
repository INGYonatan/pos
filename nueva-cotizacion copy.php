<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Nueva cotización',
  'page_identifier'   => 'nueva-cotizacion'
];

$productos_page_config_id   = 'productos';
$proveedores_page_config_id = 'proveedores';

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$data_usuario = getUserData(get_id_usuario());
$carrito_ssid = SESSION_CARRITO_NUEVA_COTIZACION;
unset($_SESSION[$carrito_ssid]);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">

  <style>
    .ui-state-active {
      background-color: red;
      font-weight: bold;
    }
  </style>
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
          <div class="row">
            <div class="col-12 col-lg-8 col-xl-8">
              <div class="card adm-card-fh card-border-top border-top-primary">
                <form class="card-header bg-transparent">
                  <div class="row">
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-cantidad">Cantidad</label>
                        <input id="atc-cantidad" class="form-control form-control-lg decimal-input" min="0" value="1" type="text">
                      </div>
                    </div>

                    <div class="col-12 col-md-8 col-lg-9 col-xl-10 ps-md-4">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-producto">Escribe el nombre del producto</label>

                        <div class="input-group">
                          <input id="atc-producto" class="form-control form-control-lg" type="text">

                          <label class="btn btn-white bg-white btn-lg" for="atc-producto">
                            <i class="fa fa-barcode"></i>
                          </label>
                        </div>
                      </div>
                    </div>
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
              <form id="<?= $page_config['page_identifier']; ?>-form-data" class="card adm-card-fh" autocomplete="off">
                <div class="card-header bg-primary">
                  <h3 class="header-title text-dark m-0">Detalles de la cotización</h3>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="id_sucursal">Sucursal<span class="text-danger">*</span></label>
                        <select id="id_sucursal" class="form-control form-select" name="id_sucursal" required>
                          <?= getBranchOfficesCatalog(getStoreId(), '--Seleccionar--', true); ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="tipo">Tipo<span class="text-danger">*</span></label>
                        <select id="tipo" class="form-control form-select" name="tipo" required>
                          <option value="">--Seleccionar--</option>
                          <option value="abierta">Abierta</option>
                          <option value="cerrada">Cerrada</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <?php $data_cliente = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo FROM {$db_dti}_clientes ORDER BY id_cliente ASC LIMIT 1")); ?>
                        <label class="form-label" for="atc-id_cliente_label">Cliente<span class="text-danger">*</span></label>
                        <input id="atc-id_cliente_label" class="form-control" value="<?= $data_cliente['nombre_completo']; ?>" type="text" <?= $data_usuario['IS_ADMIN'] === 'si' ? '' : 'readonly'; ?>>
                        <input id="atc-id_cliente" value="<?= $data_cliente['id_cliente']; ?>" type="hidden">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="observaciones">Observaciones</label>
                        <textarea id="observaciones" class="form-control" name="observaciones" rows="3"></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="card-footer bg-transparent border-0 py-0 text-end">
                  <div class="d-flex w-100 flex-column align-items-center justify-content-center flex-md-row align-items-md-start justify-content-md-end gap-2 mt-2 mb-2">
                    <div class="text-right">
                      <h3 id="carrito-total" class="m-0">Total: $0.00 MXN</h3>
                    </div>
                  </div>
                </div> -->

                <div class="card-footer bg-transparent">
                  <div class="row">
                    <div class="col-12 bg-light py-2">
                      <button class="btn btn-lg btn-block btn-primary w-100" type="submit">Realizar cotización</button>
                    </div>
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
      values: {},
      dynamicValues: () => ({
        id_sucursal: $('#id_sucursal').val(),
        tipo: $('#tipo').val(),
        id_cliente: $('#atc-id_cliente').val(),
        observaciones: $('#observaciones').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        console.log(response);

        $(`#sale-price-label-${response.id_producto}`).html(`${response.importe}`);
        $(`#precio_venta-${response.id_producto}`).html(`${response.precio_venta}`);

        $(`#carrito-subtotal`).html(`${response.subtotal}`);
        $(`#carrito-iva`).html(`${response.total_iva}`);
        $(`#carrito-total`).html(`${response.total}`);

        //$(`#precio_venta-${response.id_producto}`).val(response.precio);
      },

      onSuccessUpdateRounding: response => $(`#carrito-total`).html(`${response.total}`),

      /* onSuccessUpdateItemPrice: response => {
        $(`#sale-price-label-${response.id_producto}`).html(`${response.precio_venta_final}`);
        $(`#carrito-total`).html(`${response.total}`);
      }, */

      onSuccessSaveCart: response => {
        $(`#${PAGE_CONFIG.page_identifier}-form-data`).trigger('reset');
        if (response.pdf) window.open(response.pdf, '_blank');
      },
      onSuccessAdd: () => $('#atc-cantidad').val('1'),

      onSuccess: response => {
        console.log(response);
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

    const customersAutocomplete = new Autocomplete({
      identifier: 'atc-id_cliente_label',
      source: `${BASE_URL}/data/autocompletes/clientes.php`,
      minLength: 2,
      onSelect: customer => $('#atc-id_cliente').val(customer.id_cliente)
    });

    $(document).on('keyup', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      const callback = () => storeCart.updateItemQuantity(itemId, quantity);

      doSearch(callback);
    });

    /* $(document).on('change', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemQuantity(itemId, quantity);
    }); */

    /* $(document).on('keyup', '.price-input', function(e) {
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
    }); */

    $(document).on('keyup', '#carrito-redondeo', function(e) {
      e.stopPropagation();
      const rounding = $(this).val();

      const callback = () => storeCart.updateRounding(rounding);

      doSearch(callback);
    });

    $(document).on('change', '#carrito-redondeo', function(e) {
      e.stopPropagation();
      const rounding = $(this).val();

      const callback = () => storeCart.updateRounding(rounding);

      doSearch(callback);
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

    //$('#atc-tipo_ajuste').on('change', () => storeCart.loadCart());

    /* $('#id_sucursal').on('change', function() {
      const idSucursal = $(this).val();

      $('#filter-id-sucursal').val(idSucursal);
      $('#filter-id-sucursal').closest('form').submit();
      storeCart.cartAction('change-branch-office');
    }); */

    $('#btn-add-product').on('click', () => {
      $(`#<?= $productos_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $productos_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $productos_page_config_id; ?>');
    });

    $('#btn-add-supplier').on('click', () => {
      $(`#<?= $proveedores_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $proveedores_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $proveedores_page_config_id; ?>');
    });

    /* let delayTimer;

    const doSearch = callback => {
      clearTimeout(delayTimer);

      delayTimer = setTimeout(() => {
        !!callback && callback();
      }, 500);
    } */

    $(function() {
      searchAutocomplete.initAutocomplete();
      customersAutocomplete.initAutocomplete();
      storeCart.loadCart();
    });
  </script>
</body>

</html>