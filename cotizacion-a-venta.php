<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/quotes.helper.php';
require 'data/lib/helpers/catalogs.helper.php';

$page_config = [
  'page_title'        => 'Cotización a venta',
  'page_identifier'   => 'cotizacion-a-venta',
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

$quote_id = cleanStr($_GET['uid']);

if (!$quote_id) :
  closeSession();
  die;
endif;

$user_data                = getUserData(get_id_usuario());
$IS_ADMIN                 = $user_data['IS_ADMIN'] === 'si' ? true : false;

$cart = get_quote_data($quote_id, $user_data['id_sucursal']);

if (!$cart) :
  closeSession();
  die;
endif;

if ($cart->type != 'cerrada') :
  closeSession();
  die;
endif;

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
                      <label class="form-label text-dark" for="atc-quantity">Cantidad</label>
                      <input id="atc-quantity" class="form-control form-control-lg decimal-input" min="0" value="1" type="text">
                    </div>
                  </div>

                  <div class="col-12 col-md-8 col-lg-9 col-xl-10 ps-md-4">
                    <div class="form-group">
                      <label class="form-label text-dark" for="atc-product">Escribe el nombre del producto</label>

                      <div class="input-group">
                        <input id="atc-product" class="form-control form-control-lg" type="text">

                        <button id="btn-carrito-modal-productos" class="btn btn-white bg-white btn-lg" for="atc-product" data-bs-toggle="modal" data-bs-target="#carrito-modal-productos-modal" type="button">
                          <i class="fa fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </form>

              <div id="cart-table" class="card-body"></div>

              <!-- CARD LOADING -->
              <?php include 'src/components/card-loading.php'; ?>

              <div class="card-footer text-end">
                <button id="btn-clean-cart" class="btn btn-danger" type="button">
                  <i class="fa fa-trash"></i> Reiniciar
                </button>
              </div>
            </div>
          </div>

          <div class="col-12 col-lg-4 col-xl-4">
            <form id="details-form" class="card bk-card-fh card-border-top border-top-primary form-validate" autocomplete="off" data-validateCallback="details-form" style="overflow: visible;">
              <header class="card-header d-flex align-items-center justify-content-between gap-2 bg-transparent py-3">
                <div>
                  <h5 id="selected-branch-label" class=" mt-0 mb-1 font-20"><?= $user_data['nombre_sucursal']; ?></h5>

                  <p class="mb-0 text-muted font-14">
                    <small class="mdi mdi-circle text-success"></small>
                    <span id="selected-seller-label"><?= $user_data['nombre_completo']; ?></span>
                  </p>
                </div>

                <div class="d-flex align-items-center gap-3">
                  <?php if (checkModuleActionPermission($page_config['page_identifier'], 'corte-caja')) : ?>
                    <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Corte de caja">
                      <a id="btn-cash-cut" href="javascript:void(0)">
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
                          $is_active = $branch['id_sucursal'] == $user_data['id_sucursal'] ? 'selected' : '';
                        ?>
                          <a id="branch-<?= $branch['id_sucursal']; ?>" class="btn-select-branch <?= $is_active; ?>" data-id="<?= $branch['id_sucursal']; ?>" data-name="<?= $branch['nombre_sucursal']; ?>" href="javascript:void(0)">
                            <i class="fe-home"></i>
                            <span class="text-capitalize"><?= strtolower($branch['nombre_sucursal']); ?></span>
                          </a>
                        <?php endforeach; ?>
                      </div>
                    </article>

                    <input id="atc-branchId" name="branchId" value="<?= $user_data['id_sucursal']; ?>" type="hidden">
                  <?php endif; ?>

                  <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Usuario">
                    <?php $branch_users = getUsersByBranchOfficeId($user_data['id_sucursal']); ?>

                    <a href="javascript:void(0)">
                      <i class="fe-users"></i>
                    </a>

                    <div class="cbm-menu">
                      <?php foreach ($branch_users as $user) :
                        $is_active = $user['id_usuario'] == $user_data['id_usuario'] ? 'selected' : '';
                      ?>
                        <a id="seller-<?= $user['id_usuario']; ?>" class="btn-select-seller <?= $is_active; ?>" data-id="<?= $user['id_usuario']; ?>" data-name="<?= $user['nombre_completo']; ?>" href="javascript:void(0)">
                          <i class="fe-user"></i>
                          <span class="text-capitalize"><?= strtolower($user['nombre_completo']); ?></span>
                        </a>
                      <?php endforeach; ?>
                    </div>

                    <input id="atc-sellerId" name="sellerId" value="<?= $user_data['id_usuario']; ?>" type="hidden">
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
                    <a id="btn-close-session" href="javascript:void(0)">
                      <i class="fe-log-out text-danger"></i>
                    </a>
                  </article>
                </div>
              </header>

              <div class="card-body p-1">
                <table class="table table-sm table-bordered">
                  <tbody>
                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-customerId_label">Cliente</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <?php $customer_data = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo FROM {$db_dti}_clientes WHERE id_cliente = {$cart->customer->id} ORDER BY id_cliente ASC LIMIT 1")); ?>

                        <div class="form-group m-0">
                          <input id="atc-customerId_label" class="form-control form-control-sm" value="<?= $customer_data['nombre_completo']; ?>" type="text">
                          <input id="atc-customerId" value="<?= $customer_data['id_cliente']; ?>" type="hidden">
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-addressId">Dirección de envío</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <select id="atc-addressId" class="form-select form-control form-select-sm form-control-sm" name="addressId">
                            <?= catalog_get_customer_addresses($customer_data['id_cliente']); ?>
                          </select>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-cfdi">Uso de CFDI</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <select id="atc-cfdi" class="form-select form-control form-select-sm form-control-sm" name="cfdi" required>
                            <?= getCFDICatalog(); ?>
                            <option value="0">No requiere factura</option>
                          </select>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-cash">Efectivo</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-cash" class="form-control form-control-sm payWith" name="cash" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" type="number">
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-check">Cheque</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-check" class="form-control form-control-sm payWith" name="check" data-content="#atc-check-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                        </div>

                        <div id="atc-check-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                          <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-checkReference"><small>Referencia</small><span class="text-danger">*</span></label>
                              <input id="atc-checkReference" class="form-control form-control-sm" name="checkReference" type="text" required>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-transfer">Transferecia</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-transfer" class="form-control form-control-sm payWith" name="transfer" data-content="#atc-transfer-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                        </div>

                        <div id="atc-transfer-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                          <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-transferReference"><small>Referencia<span class="text-danger">*</span></small></label>
                              <input id="atc-transferReference" class="form-control form-control-sm" name="transferReference" type="text" required>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-creditCard">Tarjeta de crédito</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-creditCard" class="form-control form-control-sm payWith" name="creditCard" data-content="#atc-creditCard-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                        </div>

                        <div id="atc-creditCard-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                          <!-- <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-creditCardReference"><small>Referencia<span class="text-danger">*</span></small></label>
                              <input id="atc-creditCardReference" class="form-control form-control-sm" name="creditCardReference" type="text" required>
                            </div>
                          </div> -->

                          <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-creditCardNumber"><small>Número de tarjeta<span class="text-danger">*</span></small></label>
                              <input id="atc-creditCardNumber" class="form-control form-control-sm" name="creditCardNumber" type="text" required>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-debitCard">Tarjeta de débito</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-debitCard" class="form-control form-control-sm payWith" name="debitCard" data-content="#atc-debitCard-extra-inputs" step="<?= DECIMALS_CURRENCY_STEP; ?>" min="0" value="0" data-numberSwitcher type="number">
                        </div>

                        <div id="atc-debitCard-extra-inputs" class="d-flex align-items-center justify-content-start w-100 bg-light mt-1 p-1 gap-1 rounded moneySwitcher">
                          <!-- <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-debitCardReference"><small>Referencia<span class="text-danger">*</span></small></label>
                              <input id="atc-debitCardReference" class="form-control form-control-sm" name="debitCardReference" type="text" required>
                            </div>
                          </div> -->

                          <div class="flex-1">
                            <div class="form-group m-0">
                              <label class="form-label" for="atc-debitCardNumber"><small>Número de tarjeta<span class="text-danger">*</span></small></label>
                              <input id="atc-debitCardNumber" class="form-control form-control-sm" name="debitCardNumber" type="text" required>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <tr id="row-exchange" style="display: none;">
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-exchange">Cambio</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <input id="atc-exchange" class="form-control form-control-sm" name="exchange" type="text" readonly>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
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

  <!-- MODALS -->
  <?php include 'src/modals/form-discount.php'; ?>
  <?php include "src/modals/{$page_config['page_identifier']}.php"; ?>
  <?php include 'src/modals/carrito-numeros-serie.php'; ?>

  <?php
  $carrito_modal_productos_branch_id = $user_data['id_sucursal'];
  include 'src/modals/carrito-modal-productos.php';
  ?>

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

  <!-- MULTITABLE -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script>
    const getQuantity = () => $('#atc-quantity').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      values: {
        quoteId: <?= $quote_id; ?>,
        quoteFolio: '<?= $cart->folio; ?>'
      },

      dynamicValues: () => ({
        sellerId: $('#atc-sellerId').val(),
        branchId: $('#atc-branchId').val(),
        customerId: $('#atc-customerId').val(),
        addressId: $('#atc-addressId').val(),
        exchange: $('#atc-exchange').val(),
        cfdi: $('#atc-cfdi').val(),
        cash: $('#atc-cash').val(),
        check: $('#atc-check').val(),
        checkReference: $('#atc-checkReference').val(),
        transfer: $('#atc-transfer').val(),
        transferReference: $('#atc-transferReference').val(),
        creditCard: $('#atc-creditCard').val(),
        creditCardReference: $('#atc-creditCardReference').val(),
        creditCardNumber: $('#atc-creditCardNumber').val(),
        debitCard: $('#atc-debitCard').val(),
        debitCardReference: $('#atc-debitCardReference').val(),
        debitCardNumber: $('#atc-debitCardNumber').val()
      }),

      onSuccessLoad: response => $('#cart-table').html(response),

      onSuccessUpdateItemQuantity: response => {},

      onSuccessUpdateRounding: response => {
        const cartTotal = response.cartTotal;
        $('#cartTotal').html(cartTotal);

        $('#atc-cartTotal').val(strToNumber(cartTotal));

        calculateExchange()
      },

      onSuccessSaveCart: response => {
        $(`#details-form`).trigger('reset');
        window.open(response.ticket, '_blank');
        window.close();
      },

      onSuccessAdd: () => $('#atc-quantity').val('1'),

      onSuccessCartAction: response => {
        if (response.ticket) window.open(response.ticket, '_blank');
      },

      onSuccess: response => {
        console.log(response);
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        storeCart.loadCart();
      },

      onError: response => {
        console.log(response);
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        storeCart.loadCart();
      }
    });

    const searchAutocomplete = new Autocomplete({
      identifier: 'atc-product',
      source: `${BASE_URL}/data/autocompletes/productos.php`,
      minLength: 2,
      onSelect: product => storeCart.addItem(product.id_producto, getQuantity()),
      onEnter: code => storeCart.addItemWithCode(code, getQuantity()),
      useCleanOnSelect: true
    });

    const customersAutocomplete = new Autocomplete({
      identifier: 'atc-customerId_label',
      source: `${BASE_URL}/data/autocompletes/clientes.php`,
      minLength: 2,
      onSelect: customer => {
        $('#atc-customerId').val(customer.id_cliente);
        getCustomerAddresses(customer.id_cliente);
      }
    });

    const calculateDiscount = useFunc => {
      const priceData = useFunc({
        price: $(`#fd-price`).val(),
        newPrice: $(`#fd-netPrice`).val(),
        discount: $(`#fd-discount`).val()
      });

      if (!priceData) return;

      $(`#fd-price`).val(priceData.price);
      $(`#fd-netPrice`).val(priceData.newPrice);
      $(`#fd-discount`).val(priceData.discount);
    }

    const calculateExchange = () => {
      const cartTotalInput = $('#atc-cartTotal');
      const cashInput = $('#atc-cash');
      const checkInput = $('#atc-check');
      const transferInput = $('#atc-transfer');
      const creditCardInput = $('#atc-creditCard');
      const debitCardInput = $('#atc-debitCard');

      const cash = cashInput.val() ? parseFloat(cashInput.val()) : 0;
      const check = checkInput.val() ? parseFloat(checkInput.val()) : 0;
      const transfer = transferInput.val() ? parseFloat(transferInput.val()) : 0;
      const creditCard = creditCardInput.val() ? parseFloat(creditCardInput.val()) : 0;
      const debitCard = debitCardInput.val() ? parseFloat(debitCardInput.val()) : 0;

      if (cash > 0) $('#row-exchange').show();
      else $('#row-exchange').hide();

      let payWith = cash + check + transfer + creditCard + debitCard;
      let cartTotal = cartTotalInput.val() ? parseFloat(cartTotalInput.val()) : 0;

      const exchange = payWith - cartTotal;

      $('#atc-exchange').val(exchange.toFixed(DECIMALS_CURRENCY));
    }

    const getCustomerAddresses = customerId => getCatalog({
      catalogSelector: '#atc-addressId',
      resetCatalog: true,
      parameters: {
        customerId,
        action: 'get-customer-addresses'
      }
    });

    $('#fd-discount').on('keyup', () => calculateDiscount(calculatePriceWithPercentajeDiscount));
    $('#fd-netPrice').on('keyup', () => calculateDiscount(calculatePriceWithNewPrice));

    $('.payWith').on('change, keyup', () => calculateExchange());

    $('#btn-clean-cart').on('click', () => storeCart.cleanCart(true, 'Reiniciar carrito', '¿Realmente desea reiniciar el carrito?'));

    $('.btn-select-seller').on('click', function() {
      const id = $(this).attr('data-id');
      const name = capitalizeWords($(this).attr('data-name'));

      $('#selected-seller-label').html(name);
      $('#atc-sellerId').val(id);

      $('.btn-select-seller').removeClass('selected');
      $(this).addClass('selected');
    });

    $('.btn-select-branch').on('click', function() {
      const id = $(this).attr('data-id');
      const name = capitalizeWords($(this).children('span').html());

      $('#selected-branch-label').html(name);
      $('[name="branchId"]').val(id);

      $('.btn-select-branch').removeClass('selected');
      $(this).addClass('selected');
      storeCart.cleanCart(false);
    });

    $validate_callbacks['details-form'] = () => storeCart.saveCart('Venta', '¿Realmente desea realizar la venta?');

    $('#btn-inventory').on('click', () => load(1, 'pos-inventario'));
    $('#btn-today-sales').on('click', () => load(1, 'ventas-del-dia'));

    <?php if (checkModuleActionPermission($page_config['page_identifier'], 'corte-caja')) : ?>
      $('#btn-cash-cut').on('click', async () => {
        const alertResponse = await showSweetConfirm({
          title: 'Corte de caja',
          message: '¿Esta seguro de realizar el corte de caja?'
        });

        if (!alertResponse) return;

        storeCart.cartAction('corte-caja');
      });
    <?php endif; ?>

    $('#btn-close-session').on('click', async () => {
      const alertResponse = await showSweetConfirm({
        title: 'Cerrar sesión',
        message: '¿Estas seguro de cerrar sesión?'
      });

      if (!alertResponse) return;

      location.href = `${BASE_URL}/cerrar-sesion`;
    });

    $(function() {
      storeCart.loadCart();
      storeCart._initProductsModal();
      storeCart._initAddSerialNumber();
      searchAutocomplete.initAutocomplete();
      customersAutocomplete.initAutocomplete();
    });
  </script>

  <!-- VALIDATE.INIT.JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>