<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/quotes.helper.php';
require 'data/lib/helpers/catalogs.helper.php';

$page_config = [
  'page_title'        => 'POS',
  'page_identifier'   => 'pos',
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

$user_data  = getUserData(get_id_usuario());
$IS_ADMIN   = $user_data['IS_ADMIN'] === 'si' ? true : false;

$carrito_ssid = SESSION_CARRITO_POS;
unset($_SESSION[$carrito_ssid]);

$posType = $_GET["posType"] ?? "ventas";
$quoteId = 0;

if ($posType == "cotizacion-a-venta") {
  $quoteId = cleanStr($_GET["uid"]);

  if (!$quoteId) {
    closeSession();
    die;
  }

  $cart = get_quote_data($quoteId, $user_data['id_sucursal']);

  $quoteStatus = $cart->quoteStatus;

  if (!$cart) :
    closeSession();
    die;
  endif;

  if ($cart->type != 'cerrada') :
    closeSession();
    die;
  endif;

  if ($quoteStatus != "procesado") {
    $carrito_ssid             = SESSION_CARRITO_POS;
    $_SESSION[$carrito_ssid]  = $cart;

    $page_config['page_title'] = 'Cotización a venta';
  }
}
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
                  <i class="fa fa-trash"></i> Vaciar carrito
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

                  <!-- <article class="custom-button-menu" data-bs-toggle="tooltip" data-bs-placement="top" title="Cierre de caja">
                    <a id="btn-cierre-caja" data-bs-toggle="modal" data-bs-target="#cierre-caja-modal" href="javascript:void(0)">
                      <i class="fa fa-box"></i>
                    </a>
                  </article> -->

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
                        <?php
                        $byCustomerid = $cart->customer_id ? "id_cliente = {$cart->customer_id}" : "1=1";
                        $customer_data = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo, limite_credito FROM {$db_dti}_clientes WHERE {$byCustomerid} ORDER BY id_cliente ASC LIMIT 1"));
                        ?>

                        <div class="form-group m-0">
                          <div class="input-group">
                            <input id="atc-customerId_label" class="form-control form-control-sm" value="<?= $customer_data['nombre_completo']; ?>" type="text">
                            <input id="atc-customerId" value="<?= $customer_data['id_cliente']; ?>" type="hidden">

                            <div class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nuevo cliente">
                              <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#clientes-modal">+</button>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>

                    <!-- <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-addressId">Dirección de envío</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <div class="input-group">
                            <select id="atc-addressId" class="form-select form-control form-select-sm form-control-sm" name="addressId">
                              <?= catalog_get_customer_addresses($customer_data['id_cliente']); ?>
                            </select>

                            <div class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nueva dirección">
                              <button id="btn-cliente-direcciones-modal" class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#cliente-direcciones-modal">+</button>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr> -->

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-cfdi">Uso de CFDI</label>
                      </td>

                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <select id="atc-cfdi" class="form-select form-control form-select-sm form-control-sm" name="cfdi" required>
                            <?= getCFDICatalog(3); ?>
                            <option value="0">No requiere factura</option>
                          </select>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td class="align-middle">
                        <label class="form-label m-0" for="atc-paymentForm">Forma de pago</label>
                      </td>
                      <td class="align-middle" colspan="3">
                        <div class="form-group m-0">
                          <select id="atc-paymentForm" class="form-select form-control form-select-sm form-control-sm" name="paymentForm" data-content="#atc-paymentForm-info" required>
                            <option value="contado" selected>Contado</option>

                            <?php if (!$quoteId) : ?>
                              <option value="credito">Crédito</option>
                            <?php endif; ?>
                          </select>
                        </div>

                        <div id="atc-paymentForm-info" class="w-100 bg-light mt-1 p-1 gap-1 rounded" style="display: none;">
                          <?php
                          $creditLimit = $customer_data['limite_credito'];
                          $creditSaleTotal = getCreditSaleTotalByCustomerId($customer_data['id_cliente']);
                          $totalPaid = getTotalBalancePaidByCustomerId($customer_data['id_cliente']);
                          $balance = $creditSaleTotal - $totalPaid;
                          ?>
                          <div>
                            <p class="fw-bold m-0">
                              <i class="fa fa-money-bill"></i>
                              Detalles de crédito del cliente
                            </p>
                          </div>
                          <div class="d-flex gap-1">
                            <div class="form-group flex-1">
                              <label class="form-label" for="atc-creditLimit"><small>Límite</small></label>
                              <input id="atc-creditLimit" class="form-control form-control-sm" name="creditLimit" type="text" value="$<?= number_format($creditLimit, DECIMALS_CURRENCY); ?>" style="pointer-events: none;">
                            </div>

                            <div class="form-group flex-1">
                              <label class="form-label" for="atc-remainingBalance"><small>Saldo</small></label>
                              <input id="atc-remainingBalance" class="form-control form-control-sm" name="remainingBalance" type="text" value="$<?= number_format($balance, DECIMALS_CURRENCY); ?>" style="pointer-events: none;">
                            </div>

                            <div class="form-group flex-1">
                              <label class="form-label" for="atc-remainingCredit"><small>Disponible</small></label>
                              <input id="atc-remainingCredit" class="form-control form-control-sm" name="remainingCredit" type="text" value="$<?= number_format($creditLimit - $creditSaleTotal, DECIMALS_CURRENCY); ?>" style="pointer-events: none;">
                            </div>
                          </div>
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
                              <input id="atc-checkReference" class="form-control form-control-sm" name="checkReference" type="text">
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
                              <input id="atc-transferReference" class="form-control form-control-sm" name="transferReference" type="text">
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
                              <input id="atc-creditCardNumber" class="form-control form-control-sm" name="creditCardNumber" type="text">
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
                              <input id="atc-debitCardNumber" class="form-control form-control-sm" name="debitCardNumber" type="text">
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

                <div class="row">
                  <div class="col-12 px-3">
                    <div class="form-group">
                      <label class="form-label" for="atc-comments">Comentarios</label>
                      <textarea id="atc-comments" class="form-control" name="comments" rows="3"></textarea>
                    </div>
                  </div>
                </div>

                <div class="px-2">
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" value="si" name="sendTicketByEmail" id="send-ticket-by-email">
                    <label class="form-check-label" for="send-ticket-by-email">
                      ¿Enviar ticket por correo al cliente?
                    </label>
                  </div>

                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="si" name="createInvoice" id="create-invoice">
                    <label class="form-check-label" for="create-invoice">
                      ¿Generar factura?
                    </label>
                  </div>
                </div>
              </div>

              <div class="card-footer bg-transparent">
                <div class="row">
                  <div class="col-12 bg-light py-2">
                    <button id="btn-pos" class="btn btn-lg btn-block btn-primary w-100" type="submit">Cobrar venta</button>
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
  <?php /* include 'src/modals/cierre_caja.php'; */ ?>
  <?php /* include 'src/modals/apertura_caja.php'; */ ?>
  <?php include 'src/modals/form-discount.php'; ?>
  <?php include "src/modals/{$page_config['page_identifier']}.php"; ?>
  <?php include 'src/modals/carrito-numeros-serie.php'; ?>

  <?php
  $clientes_modal_page_id = "clientes";
  $clientes_modal_origin  = "pos";
  include __DIR__ . "/src/modals/clientes.php";
  ?>

  <?php
  $modal_cliente_direcciones_id     = "cliente-direcciones";
  $modal_cliente_direcciones_origin = "pos";
  include __DIR__ . "/src/modals/cliente-direcciones.php";
  ?>

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

  <script src="<?= BASE_URL; ?>/src/main/address-autocomplete.js"></script>

  <script>
    $("#btn-cliente-direcciones-modal").on("click", () => {
      const customerId = $('#atc-customerId').val();
      $("#cliente-direcciones-modal input[name=id_cliente]").val(customerId);
    });

    const getQuantity = () => $('#atc-quantity').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      withSerialNumbersCatalog: true,
      values: {},

      dynamicValues: () => ({
        sellerId: $('#atc-sellerId').val(),
        branchId: $('#atc-branchId').val(),
        customerId: $('#atc-customerId').val(),
        //addressId: $('#atc-addressId').val(),
        exchange: $('#atc-exchange').val(),
        cfdi: $('#atc-cfdi').val(),
        paymentForm: $('#atc-paymentForm').val(),
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
        debitCardNumber: $('#atc-debitCardNumber').val(),
        createInvoice: $('#create-invoice').is(':checked') ? 'si' : 'no',
        sendTicketByEmail: $('#send-ticket-by-email').is(':checked') ? 'si' : 'no',
        comments: $('#atc-comments').val(),
        quoteId: <?= $quoteId; ?>
      }),

      onSuccessLoad: response => $('#cart-table').html(response),

      // onSuccessUpdateItemQuantity: response => {
      //   $("#atc-cash").trigger("keyup");
      // },

      // onSuccessUpdateItemPrice: response => {
      //   $("#atc-cash").trigger("keyup");
      // },

      onSuccessUpdateRounding: response => {
        const cartTotal = response.cartTotal;
        $('#cartTotal').html(cartTotal);

        $('#atc-cartTotal').val(strToNumber(cartTotal));

        calculateExchange()
      },

      onSuccessSaveCart: response => {
        <?php if (!$quoteId) : ?>
          //$(`#details-form`).trigger('reset');
          if (!response?.folio) window.open(response.ticket, '_blank');
          if (response?.folio) window.open(`${BASE_URL}/facturas/nueva?tipo_factura=ingreso&folio=${response.folio}`, '_blank');

          location.reload();
        <?php endif; ?>

        <?php if ($quoteId) : ?>
          // abrir ventas
          window.open(`${BASE_URL}/ventas`, `_blank`);

          if (!response?.folio) window.open(response.ticket, '_blank');
          if (response?.folio) window.open(`${BASE_URL}/facturas/nueva?tipo_factura=ingreso&folio=${response.folio}`, '_blank');

          // Cerrar pestaña
          // Quitar de la url el uid de la cotización para evitar cargar la cotización nuevamente en caso de recargar la página
          // const url = new URL(window.location);
          // url.searchParams.delete('uid');
          // window.history.replaceState({}, '', url);
          // location.reload();
          //location.href = `${BASE_URL}/pos`;

          window.location.replace(`${BASE_URL}/pos`); // Redirigir a la página principal del POS, recargando la página sin mantener el historial para evitar volver a cargar la cotización al hacer clic en "Volver atrás"
        <?php endif; ?>
      },

      onSuccessAdd: () => $('#atc-quantity').val('1'),

      onSuccessCartAction: response => {
        if (response.ticket) window.open(response.ticket, '_blank');
      },

      onSuccessUpdateItemPrice: response => {
        storeCart.loadCart();
      },

      onSuccess: response => {
        //console.log(response);
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

        if (response.alertMessage) showSweetAlert({
          title: 'Error',
          message: response.alertMessage,
          icon: 'error'
        });

        //storeCart.loadCart();
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

        const creditLimit = customer?.creditLimit;
        const creditBalance = customer?.creditBalance;
        const remainingCredit = customer?.remainingCredit;

        $('#atc-creditLimit').val(creditLimit);
        $('#atc-remainingBalance').val(creditBalance);
        $('#atc-remainingCredit').val(remainingCredit);
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

      $('#atc-exchange').val(exchange >= 0 ? exchange.toFixed(DECIMALS_CURRENCY) : 0);
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

  <script>
    $("#atc-paymentForm").on("change", function() {
      const paymentForm = $(this).val();
      if (paymentForm === "credito") {
        $("#atc-paymentForm-info").show();
      } else {
        $("#atc-paymentForm-info").hide();
      }
    });
  </script>

  <script>
    $(document).on("submit", ".update-price-form", function(e) {
      e.preventDefault();

      const itemId = $(this).find("[name='itemId']").val();
      const price = $(this).find("[name='salePrice']").val();

      storeCart.updateItemPrice(itemId, price);
    });
  </script>

  <!-- VALIDATE.INIT.JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>