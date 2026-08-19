<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/cotizaciones.helpers.php';

$page_config = [
  'page_title'        => 'Cotización a venta',
  'page_identifier'   => 'cotizacion-a-venta'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$quote_id = cleanStr($_GET['uid']);

if (!$quote_id) :
  closeSession();
  die;
endif;

$cart = create_quote_cart($quote_id);

if (!$cart) :
  closeSession();
  die;
endif;

if ($cart->type != 'cerrada') :
  closeSession();
  die;
endif;

$data_usuario             = getUserData(get_id_usuario());
$IS_ADMIN                 = $data_usuario['IS_ADMIN'] === 'si' ? true : false;

$carrito_ssid             = SESSION_CARRITO_COTIZACION_A_VENTA;
$_SESSION[$carrito_ssid]  = $cart;
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
    <div class="content">
      <div class="container-fluid">
        <div class="row mt-2">
          <div class="col-12 col-lg-8 col-xl-8">
            <div class="card bk-card-fh card-border-top border-top-primary">
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

                  <input id="atc-id_vendedor" name="id_vendedor" value="<?= $data_usuario['id_usuario']; ?>" type="hidden">

                  <?php if ($IS_ADMIN) : ?>
                    <input id="atc-id_sucursal" name="id_sucursal" value="<?= $data_usuario['id_sucursal']; ?>" type="hidden">
                  <?php endif; ?>
                </div>
              </form>

              <div id="tabla-carrito" class="card-body"></div>

              <!-- CARD LOADING -->
              <?php include 'src/components/card-loading.php'; ?>

              <div class="card-footer text-end">
                <button id="btn-clean-cart" class="btn btn-danger" type="button">
                  <i class="fa fa-trash"></i> Reiniciar carrito
                </button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4 col-xl-4">
            <form id="details-form" class="card bk-card-fh card-border-top border-top-primary" autocomplete="off" style="overflow: visible;">
              <header class="card-header d-flex align-items-center justify-content-between gap-2 bg-transparent py-3">
                <div>
                  <h5 id="nombre-sucursal-seleccionado" class=" mt-0 mb-1 font-20 text-uppercase"><?= $data_usuario['nombre_sucursal']; ?></h5>

                  <p class="mb-0 text-muted font-14">
                    <small class="mdi mdi-circle text-success"></small>
                    <span id="nombre-usuario-seleccionado"><?= $data_usuario['nombre_completo']; ?></span>
                  </p>
                </div>

                <div class="d-flex align-items-center gap-3">
                  <?php if (checkModuleActionPermission($page_config['page_identifier'], 'corte-caja')) : ?>
                    <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Corte de caja">
                      <a id="corte-caja" href="javascript:void(0)">
                        <i class="fas fa-cash-register"></i>
                      </a>
                    </article>
                  <?php endif; ?>

                  <?php if ($IS_ADMIN) : ?>
                    <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Sucursal">
                      <?php $branch_offices = getBranchOfficesData(); ?>

                      <a href="javascript:void(0)">
                        <i class="fe-home"></i>
                      </a>

                      <div class="cbm-menu">
                        <?php foreach ($branch_offices as $branch) :
                          $is_active = $branch['id_sucursal'] == $data_usuario['id_sucursal'] ? 'selected' : '';
                        ?>
                          <a id="<?= $branch['id_sucursal']; ?>" class="seleccionar-sucursal <?= $is_active; ?>" data-name="<?= $branch['nombre_sucursal']; ?>" href="javascript:void(0)">
                            <i class="fe-home"></i>
                            <?= $branch['nombre_sucursal']; ?>
                          </a>
                        <?php endforeach; ?>
                      </div>
                    </article>
                  <?php endif; ?>

                  <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Usuario">
                    <?php $branch_users = getUsersByBranchOfficeId($data_usuario['id_sucursal']); ?>

                    <a href="javascript:void(0)">
                      <i class="fe-users"></i>
                    </a>

                    <div class="cbm-menu">
                      <?php foreach ($branch_users as $user) :
                        $is_active = $user['id_usuario'] == $data_usuario['id_usuario'] ? 'selected' : '';
                      ?>
                        <a id="<?= $user['id_usuario']; ?>" class="seleccionar-usuario <?= $is_active; ?>" data-name="<?= $user['nombre_completo']; ?>" href="javascript:void(0)">
                          <i class="fe-user"></i>
                          <span class="text-capitalize"><?= strtolower($user['nombre_completo']); ?></span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  </article>

                  <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Ventas de día">
                    <a id="btn-today-sales" data-bs-toggle="modal" data-bs-target="#modal-today-sales" href="javascript:void(0)">
                      <i class="fa fa-shopping-cart"></i>
                    </a>
                  </article>

                  <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Inventario">
                    <a id="btn-inventory" data-bs-toggle="modal" data-bs-target="#modal-inventory" href="javascript:void(0)">
                      <i class="fa fa-box"></i>
                    </a>
                  </article>

                  <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Cerrar sesión">
                    <a id="btn-cerrar-sesion" href="javascript:void(0)">
                      <i class="fe-log-out text-danger"></i>
                    </a>
                  </article>
                </div>
              </header>

              <div class="card-body">
                <?php $data_cliente = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo FROM {$db_dti}_clientes ORDER BY id_cliente ASC LIMIT 1")); ?>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <div class="input-group align-items-center gap-2">
                        <label class="form-label m-0" for="atc-id_cliente_label" style="width: 30%;">CLIENTE</label>
                        <input id="atc-id_cliente_label" class="form-control" value="<?= $data_cliente['nombre_completo']; ?>" type="text" <?= $IS_ADMIN ? '' : 'readonly'; ?>>
                        <input id="atc-id_cliente" value="<?= $data_cliente['id_cliente']; ?>" type="hidden">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <div class="input-group align-items-center gap-2">
                        <label class="form-label m-0" for="atc-pago_con" style="width: 30%;">PAGÓ CON</label>
                        <input id="atc-pago_con" class="form-control" name="pago_con" type="number" required>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-12">
                    <div class="form-group">
                      <div class="input-group align-items-center gap-2">
                        <label class="form-label m-0" for="atc-cambio" style="width: 30%;">CAMBIO</label>
                        <input id="atc-cambio" class="form-control" name="cambio" type="text" readonly>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="card-footer bg-transparent">
                <div class="row">
                  <div class="col-12 bg-light py-2">
                    <button class="btn btn-lg btn-block btn-primary w-100" type="submit">Cobrar venta</button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
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

  <!-- CART -->
  <script src="<?= BASE_URL; ?>/src/plugins/cart/main.js"></script>

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <!-- VALIDATE.INIT.JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const getQuantity = () => $('#atc-cantidad').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      values: {
        id_cotizacion: <?= $quote_id; ?>
      },
      dynamicValues: () => ({
        id_vendedor: $('#atc-id_vendedor').val(),
        id_sucursal: $('#atc-id_sucursal').val(),
        id_cliente: $('#atc-id_cliente').val(),
        pago_con: $('#atc-pago_con').val(),
        cambio: $('#atc-cambio').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        console.log(response);

        $(`#sale-price-label-${response.id_producto}`).html(`${response.importe}`);
        $(`#precio_venta-${response.id_producto}`).html(`${response.precio_venta}`);
        $(`#precio_venta_in-${response.id_producto}`).val(response.precio_neto_no_format);
        $(`#precio_venta_mod-${response.id_producto}`).val(response.precio_venta_no_format);
        $(`#precio_neto-${response.id_producto}`).html(response.precio_neto);

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
        //$(`#${PAGE_CONFIG.page_identifier}-form-data`).trigger('reset');
        if (response.pdf) window.open(response.pdf, '_blank');
        //location.reload();
        location.href = `${BASE_URL}/<?= $rollback; ?>`;
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

    $('#id_sucursal').on('change', function() {
      const idSucursal = $(this).val();

      $('#filter-id-sucursal').val(idSucursal);
      $('#filter-id-sucursal').closest('form').submit();
      storeCart.cartAction('change-branch-office');
    });

    let delayTimer;

    const doSearch = callback => {
      clearTimeout(delayTimer);

      delayTimer = setTimeout(() => {
        !!callback && callback();
      }, 500);
    }

    $(function() {
      searchAutocomplete.initAutocomplete();
      //customersAutocomplete.initAutocomplete();
      storeCart.loadCart();
    });
  </script>
</body>

</html>