<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'      => 'POS',
  'page_identifier' => 'pos',
  'tables_config'     => [
    'pos-inventario' => [
      'identifier' => 'pos-inventario'
    ],
    'ventas-del-dia' => [
      'identifier' => 'ventas-del-dia'
    ]
  ]
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$id_sucursal            = getSessionBranchOfficeId();
$sucursales             = getBranchOfficesData();
$sucursal_usuarios      = getUsersByBranchOfficeId($id_sucursal);
$data_usuario           = getUserData(get_id_usuario());

$usuario_seleccionado   = [];
$sucursal_seleccionado  = [];
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
    <div class="col-12">
      <div class="content">
        <div class="container-fluid">
          <div class="row mt-2">
            <div class="col-12 col-lg-8 col-xl-8">
              <div class="card bk-card-fh">
                <form class="card-header bg-primary">
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

                    <input id="atc-id_vendedor" name="id_vendedor" value="<?= get_id_usuario(); ?>" type="hidden">

                    <?php if ($data_usuario['IS_ADMIN'] === 'si') : ?>
                      <input id="atc-id_sucursal" name="id_sucursal" value="<?= $id_sucursal; ?>" type="hidden">
                    <?php endif; ?>
                  </div>
                </form>

                <div id="tabla-carrito" class="card-body"></div>

                <div class="card-footer">
                  <div class="d-flex align-items-center justify-content-end">
                    <a id="btn-clean-cart" class="btn btn-danger" href="#">
                      <i class="fa fa-trash me-1"></i>
                      Vaciar carrito
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card bk-card-fh" style="overflow: visible;">
                <div class="card-header bg-white">
                  <div class="d-flex py-1">
                    <div class="flex-1">
                      <h5 id="nombre-sucursal-seleccionado" class=" mb-0 font-20 text-uppercase">
                        <?= $data_usuario['nombre_sucursal']; ?>
                      </h5>

                      <p class="mt-1 mb-0 text-muted font-14">
                        <small class="mdi mdi-circle text-success"></small>
                        <span id="nombre-usuario-seleccionado"><?= $data_usuario['nombre_completo']; ?></span>
                      </p>
                    </div>

                    <div id="change-user-tooltip-container">
                      <?php if (checkModuleActionPermission($page_config['page_identifier'], 'corte-caja')) : ?>
                        <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                          <a id="corte-caja" class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" href="#" role="button">
                            <i class="fa fa-cash-register" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Corte de caja"></i>
                          </a>
                        </li>
                      <?php endif; ?>

                      <?php if ($data_usuario['IS_ADMIN'] === 'si') : ?>
                        <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                          <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <i class="fe-home" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambiar sucursal"></i>
                          </a>

                          <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                            <div class="p-2">
                              <div class="row g-0">
                                <?php foreach ($sucursales as $key => $row) :
                                  $is_active = $row['id_sucursal'] === $id_sucursal;
                                  if ($is_active) $sucursal_seleccionado = $row;
                                ?>
                                  <div class="col-6 <?= $is_active ? '' : ''; ?>">
                                    <a id="<?= $row['id_sucursal']; ?>" class="dropdown-icon-item px-2 seleccionar-sucursal <?= $is_active ? 'bg-primary' : ''; ?>" data-name="<?= $row['nombre_sucursal']; ?>" type="button" href="javascript:void(0)">
                                      <img class="rounded-circle" src="https://placehold.co/50" alt="<?= $row['nombre_sucursal']; ?>">
                                      <span><?= $row['nombre_sucursal']; ?></span>
                                    </a>
                                  </div>
                                <?php endforeach; ?>

                                <script>
                                  document.getElementById('nombre-sucursal-seleccionado').innerHTML = `<?= $sucursal_seleccionado['nombre_sucursal']; ?>`;
                                </script>
                              </div>
                            </div>
                          </div>
                        </li>
                      <?php endif; ?>

                      <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                          <i class="fe-users" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Cambiar usuario"></i>
                        </a>

                        <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                          <div class="p-2">
                            <?php
                            $id_sucursal          = getSessionBranchOfficeId();
                            $sucursal_usuarios    = getUsersByBranchOfficeId($id_sucursal);
                            $usuario_seleccionado = [];
                            ?>

                            <div class="row g-0">
                              <?php foreach ($sucursal_usuarios as $key => $row) :
                                $is_active = $row['id_usuario'] === get_id_usuario();
                                if ($is_active) $usuario_seleccionado = $row;
                              ?>
                                <div class="col-6 <?= $is_active ? '' : ''; ?>">
                                  <a id="<?= $row['id_usuario']; ?>" class="dropdown-icon-item px-2 seleccionar-usuario <?= $is_active ? 'bg-primary' : ''; ?>" data-name="<?= $row['nombre_completo']; ?>" type="button" href="javascript:void(0)">
                                    <img class="rounded-circle" src="https://placehold.co/50" alt="<?= $row['nombre_completo']; ?>">
                                    <span><?= $row['nombre_completo']; ?></span>
                                  </a>
                                </div>
                              <?php endforeach; ?>

                              <?php if ($usuario_seleccionado['nombre_completo']) : ?>
                                <script>
                                  document.getElementById('nombre-usuario-seleccionado').innerHTML = `<?= $usuario_seleccionado['nombre_completo']; ?>`;
                                </script>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </li>

                      <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                        <a id="btn-today-sales" class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#modal-today-sales" href="javascript:void(0)" role="button">
                          <i class="fa fa-shopping-cart" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Ventas del día"></i>
                        </a>
                      </li>

                      <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                        <a id="btn-inventory" class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" data-bs-toggle="modal" data-bs-target="#modal-inventory" href="javascript:void(0)" role="button">
                          <i class="fa fa-box" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Inventario"></i>
                        </a>
                      </li>

                      <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                        <a id="btn-cerrar-sesion" class="nav-link dropdown-toggle arrow-none waves-effect waves-light text-reset font-19 py-1 px-2 d-inline-block" href="#" role="button">
                          <i class="fe-log-out text-danger" data-bs-container="#change-user-tooltip-container" data-bs-toggle="tooltip" data-bs-placement="top" title="Cerrar sesión"></i>
                        </a>
                      </li>
                    </div>
                  </div>
                </div>

                <div class="card-body">
                  <?php $data_cliente = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo FROM {$db_dti}_clientes ORDER BY id_cliente ASC LIMIT 1")); ?>

                  <div>
                    <div class="row">
                      <div class="col-12 text-start">
                        <div class="form-group form-group-sm">
                          <div class="input-group input-group-sm align-items-center gap-2">
                            <label class="form-label m-0" for="atc-id_cliente_label" style="width: 30%;">CLIENTE</label>
                            <input id="atc-id_cliente_label" class="form-control" value="<?= $data_cliente['nombre_completo']; ?>" type="text" <?= $data_usuario['IS_ADMIN'] === 'si' ? '' : 'readonly'; ?>>
                            <input id="atc-id_cliente" value="<?= $data_cliente['id_cliente']; ?>" type="hidden">
                          </div>
                        </div>

                        <div class="form-group form-group-sm">
                          <div class="input-group input-group-sm align-items-center gap-2">
                            <label class="form-label m-0" for="atc-pago_con" style="width: 30%;">PAGÓ CON</label>
                            <input id="atc-pago_con" class="form-control" name="pago_con" type="text" required>
                          </div>
                        </div>

                        <div class="form-group form-group-sm">
                          <div class="input-group input-group-sm align-items-center gap-2">
                            <label class="form-label m-0" for="atc-cambio" style="width: 30%;">CAMBIO</label>
                            <input id="atc-cambio" class="form-control" name="cambio" type="text" readonly>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="ldg-carrito-loading" class="card-loading" style="display: none;">
                  <div class="dimmer active">
                    <div class="spinner-border text-primary m-2" role="status"></div>
                  </div>
                </div>

                <div class="card-footer bg-white">
                  <div class="d-flex w-100 flex-column align-items-center justify-content-center flex-md-row align-items-md-start justify-content-md-end gap-2 mt-2 mb-2">
                    <div class="text-right">
                      <h3 id="carrito-total" class="m-0">Total: $0.00 MXN</h3>
                      <input id="atc-total" value="0" type="hidden">
                    </div>
                  </div>

                  <div class="row">
                    <div class="col">
                      <div class="mt-2 bg-light p-3 rounded">
                        <button id="btn-save-cart" class="btn w-100 btn-lg btn-primary waves-effect waves-light" type="button">
                          Cobrar venta
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- END wrapper -->

  <!-- MODALS -->
  <?php include "src/modals/{$page_config['page_identifier']}.php"; ?>

  <!-- PAGE LOADINGS -->
  <?php include 'src/components/page-loadings.php'; ?>

  <!-- REQUIRED SCRIPTS -->
  <?php include 'src/components/required-scripts.php'; ?>

  <!-- APP JS -->
  <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <!-- CART -->
  <script src="<?= BASE_URL; ?>/src/plugins/cart/main.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const getQuantity = () => $('#atc-cantidad').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      loadingId: 'ldg-carrito',

      dynamicValues: () => ({
        id_vendedor: $('#atc-id_vendedor').val(),
        id_sucursal: $('#atc-id_sucursal').val(),
        id_cliente: $('#atc-id_cliente').val(),
        pago_con: $('#atc-pago_con').val(),
        cambio: $('#atc-cambio').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        $(`#total-${response.id_producto}`).html(response.total);
        $(`#carrito-total`).html(response.carrito_total);
        $(`#atc-total`).val(response.carrito_total_number);
        $(`#atc-pago_con`).val(response.carrito_total_number);
        calcularCambio(response.carrito_total_number, response.carrito_total_number);
      },

      onSuccessCartAction: response => {
        if (response.type === 'lector') storeCart.addItem(response.id_producto, getQuantity());
        if (response.ticket) window.open(response.ticket, '_blank');
      },

      onErrorCartAction: response => {
        if (response.type === 'lector') {
          $('#atc-producto').val('');
          $('#atc-producto').trigger('keyup');
        }
      },

      onSuccessSaveCart: response => {
        if (response.ticket) window.open(response.ticket, '_blank');
      },

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
      onEnter: str => storeCart.cartAction('verificar-producto', str),
      useCleanOnSelect: true
    });

    const customersAutocomplete = new Autocomplete({
      identifier: 'atc-id_cliente_label',
      source: `${BASE_URL}/data/autocompletes/clientes.php`,
      minLength: 2,
      onSelect: customer => $('#atc-id_cliente').val(customer.id_cliente)
    });

    <?php if ($data_usuario['IS_ADMIN'] === 'si') : ?>
      $('.seleccionar-sucursal').on('click', function() {
        const uid = $(this).attr('id');
        const name = $(this).attr('data-name');

        $('#atc-id_sucursal').val(`${uid}`);
        $('[name="id_sucursal"]').val(`${uid}`);
        $('#nombre-sucursal-seleccionado').html(name);

        $('.seleccionar-sucursal').removeClass('bg-primary');
        $(`#${uid}`).addClass('bg-primary');

        storeCart.cartAction('change-branch-office');
        //load(1, PAGE_CONFIG.page_identifier);
      });
    <?php endif; ?>

    $('.seleccionar-usuario').on('click', function() {
      const uid = $(this).attr('id');
      const name = $(this).attr('data-name');

      $('#atc-id_vendedor').val(`${uid}`);
      $('#nombre-usuario-seleccionado').html(name);

      $('.seleccionar-usuario').removeClass('bg-primary');
      $(`#${uid}`).addClass('bg-primary');
    });

    $(document).on('keyup', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemQuantity(itemId, quantity);
    });

    $(document).on('change', '.quantity-input', function(e) {
      e.stopPropagation();

      const quantity = $(this).val();
      const itemId = $(this).attr('data-itemId');

      storeCart.updateItemQuantity(itemId, quantity);
    });

    $(document).on('click', '.btn-remove-item', function(e) {
      e.stopPropagation();

      const itemId = $(this).attr('data-itemId');

      storeCart.removeItem(itemId);
    });

    /* $(document).on('click', '.autocomplete-item', function(e) {
      e.stopPropagation();

      const itemId = $(this).attr('data-itemId');
      storeCart.addItem(itemId, 1);
    }); */

    $('#btn-clean-cart').on('click', () => storeCart.cleanCart());
    $('#btn-save-cart').on('click', () => storeCart.saveCart('Venta', '¿Realmente desea realizar la venta?'));

    const calcularCambio = (total, pagoCon) => {
      const cambio = pagoCon - total;

      /* if (cambio < 0)  */
      $('#atc-cambio').val(0.00);
      /* if (cambio >= 0) */
      $('#atc-cambio').val(cambio.toFixed(DECIMALS_CURRENCY));
    }

    $('#atc-pago_con').on('change', () => calcularCambio($('#atc-total').val(), $('#atc-pago_con').val()));
    $('#atc-pago_con').on('keyup', () => calcularCambio($('#atc-total').val(), $('#atc-pago_con').val()));

    $(function() {
      customersAutocomplete.initAutocomplete();
      searchAutocomplete.initAutocomplete();

      storeCart.loadCart();

      setTimeout(() => {
        $('#atc-producto').focus();
      }, 500);
    });

    <?php if (checkModuleActionPermission($page_config['page_identifier'], 'corte-caja')) : ?>
      $('#corte-caja').on('click', async () => {
        const alertResponse = await showSweetConfirm({
          title: 'Corte de caja',
          message: '¿Esta seguro de realizar el corte de caja?'
        });

        if (!alertResponse) return;

        storeCart.cartAction('corte-caja');
      });
    <?php endif; ?>

    $('#btn-cerrar-sesion').on('click', async () => {
      const alertResponse = await showSweetConfirm({
        title: 'Cerrar sesión',
        message: '¿Esta seguro de cerrar sesión?'
      });

      if (!alertResponse) return;

      location.href = `${BASE_URL}/cerrar-sesion`;
    });
  </script>

  <script>
    $('#btn-today-sales').on('click', () => load(1, 'ventas-del-dia'));
    $('#btn-inventory').on('click', () => load(1, 'pos-inventario'));

    $(document).on('click', '.btn-ver-productos', function() {
      const data = JSON.parse($(this).attr('data-row'));
      const productos = data.productos;

      $('#tabla-ver-productos').html(productos);
    });
  </script>
</body>

</html>