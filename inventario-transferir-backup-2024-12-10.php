<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Transferencias entre sucursales',
  'page_identifier'   => 'inventario-transferir',
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$carrito_ssid             = SESSION_CARRITO_TRANSFERIR_INVENTARIO;
$id_sucursal_origen_ssid  = SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_ORIGEN;
$id_sucursal_destino_ssid = SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_DESTINO;
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
          <div class="row">
            <div class="col-12 col-lg-4 col-xl-4">
              <form id="<?= $page_config['page_identifier']; ?>-form-data" class="card border-primary border adm-card-fh form-validate" autocapitalize="off">
                <div class="card-header">
                  <h3 class="card-title text-dark m-0">Detalles de transferencia</h3>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="id_sucursal_origen">Sucursal origen<span class="text-danger">*</span></label>
                        <select id="id_sucursal_origen" class="form-control form-select" name="id_sucursal_origen" data-modalProductosBranchId required>
                          <?= getBranchOfficesCatalog($_SESSION[$id_sucursal_origen_ssid] ? $_SESSION[$id_sucursal_origen_ssid] : 1, '', true, false); ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="id_sucursal_destino">Sucursal destino<span class="text-danger">*</span></label>
                        <select id="id_sucursal_destino" class="form-control form-select" name="id_sucursal_destino" required>
                          <?= getBranchOfficesCatalog($_SESSION[$id_sucursal_destino_ssid], '', true, false); ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="fecha">Fecha<span class="text-danger">*</span></label>
                        <input id="fecha" class="form-control datepicker" name="fecha" value="<?= date('d/m/Y'); ?>" type="text" readonly required>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="observaciones">Observaciones</label>
                        <textarea id="observaciones" class="form-control" name="observaciones" rows="8"></textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            <div class="col-12 col-lg-8 col-xl-8">
              <div class="row">
                <div class="col-12">
                  <div class="card border-primary border adm-card-fh">
                    <form class="card-header">
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

                              <button id="btn-carrito-modal-productos" class="btn btn-white bg-white btn-lg" for="atc-product" data-bs-toggle="modal" data-bs-target="#carrito-modal-productos-modal" type="button">
                                <i class="fa fa-search"></i>
                              </button>
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

                      <button id="btn-save-cart" class="btn btn-lg btn-primary" type="button">
                        <i class="fa fa-check-circle me-1"></i> Realizar transferencia
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php
      $carrito_modal_productos_branch_id = $_SESSION[$id_sucursal_origen_ssid] ? $_SESSION[$id_sucursal_origen_ssid] : 1;
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

  <!-- MULTIDATATABLE -->
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
      dynamicValues: () => ({
        id_sucursal_origen: $('#id_sucursal_origen').val(),
        id_sucursal_destino: $('#id_sucursal_destino').val(),
        observaciones: $('#observaciones').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        $(`#new-stock-origin-label-${response.id_producto}`).html(`${response.stock_origen_final}`);
        $(`#new-stock-destiny-label-${response.id_producto}`).html(`${response.stock_destino_final}`);
      },

      onSuccessAdd: () => $('#atc-cantidad').val('1'),

      onSuccess: response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        storeCart.loadCart();
      },

      onSuccessSaveCart: response => {
        if (response.ticket) window.open(response.ticket, '_blank');
        $("#inventario-transferir-form-data").trigger("reset");
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

    $(document).on('click', '.btn-remove-item', function(e) {
      e.stopPropagation();

      const itemId = $(this).attr('data-itemId');

      storeCart.removeItem(itemId);
    });

    $('#btn-clean-cart').on('click', () => storeCart.cleanCart());
    $('#btn-save-cart').on('click', () => storeCart.saveCart('Transferir productos', '¿Realmente desea realizar la transferencia?'));

    $('#id_sucursal_origen').on('change', () => storeCart.cartAction('update-sucursal-origen'));
    $('#id_sucursal_destino').on('change', () => storeCart.cartAction('update-sucursal-destino'));

    $(function() {
      searchAutocomplete.initAutocomplete();
      storeCart.loadCart();
      storeCart._initProductsModal();
    });
  </script>
</body>

</html>