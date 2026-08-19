<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/quotes.helper.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Editar cotización',
  'page_identifier'   => 'editar-cotizacion'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$quote_id = cleanStr($_GET['uid']);
$rollback = cleanStr($_GET['rollback']);

if (!$quote_id || !$rollback) :
  closeSession();
  die;
endif;

$cart = get_quote_data($quote_id);

if (!$cart) :
  closeSession();
  die;
endif;

$data_usuario            = getUserData(get_id_usuario());
$carrito_ssid            = SESSION_CARRITO_EDITAR_COTIZACION;
$_SESSION[$carrito_ssid] = $cart;
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
            "pageTitle"       => "Editar cotización",
            "pageDescription" => "Edita la cotización para tu sucursal"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-8 col-xl-8">
              <div class="card card-border-top border-top-primary">
                <form class="card-header bg-transparent">
                  <div class="row">
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-quantity">Cantidad</label>
                        <input id="atc-quantity" class="form-control form-control-lg decimal-input" min="1" value="1" type="text">
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
              <form id="details-form" class="card" autocomplete="off">
                <div class="card-header bg-primary">
                  <h3 class="header-title text-dark m-0">Detalles de la cotización</h3>
                </div>

                <div class="card-body">
                  <div class="row">
                    <?php if ($IS_ADMIN) : ?>
                      <div class="col-12 col-md-6">
                        <div class="form-group">
                          <label class="form-label" for="atc-branchId">Sucursal<span class="text-danger">*</span></label>
                          <select id="atc-branchId" data-modalProductosBranchId class="form-control form-select" name="atc-branchId" required>
                            <?= getBranchOfficesCatalog($cart->branch_id, '--Seleccionar--', true); ?>
                          </select>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="atc-type">Tipo<span class="text-danger">*</span></label>
                        <select id="atc-type" class="form-control form-select" name="atc-type" required>
                          <option value="">--Seleccionar--</option>
                          <option value="abierta" <?= $cart->type == 'abierta' ? 'selected' : ''; ?>>Abierta</option>
                          <option value="cerrada" <?= $cart->type == 'cerrada' ? 'selected' : ''; ?>>Cerrada</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <?php $customer_data = mysqli_fetch_assoc(mysqli_query($mysqli, "SELECT id_cliente, nombre_completo FROM {$db_dti}_clientes WHERE id_cliente = {$cart->customer->id} ORDER BY id_cliente ASC LIMIT 1")); ?>
                        <label class="form-label" for="atc-customerId_label">Cliente<span class="text-danger">*</span></label>
                        <input id="atc-customerId_label" class="form-control" value="<?= $customer_data['nombre_completo']; ?>" type="text">
                        <input id="atc-customerId" value="<?= $customer_data['id_cliente']; ?>" type="hidden">
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="atc-observations">Observaciones</label>
                        <textarea id="atc-observations" class="form-control" name="atc-observations" rows="8"><?= $cart->observations; ?></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-footer bg-transparent">
                  <div class="row">
                    <div class="col-12 bg-light py-2">
                      <button class="btn btn-lg btn-block btn-primary w-100" type="submit">Guardar cambios</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/form-discount.php'; ?>

      <?php
      $carrito_modal_productos_branch_id = $cart->branch_id;
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

  <!-- VALIDATE JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const getQuantity = () => $('#atc-quantity').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      values: {
        quoteId: <?= $quote_id ?>
      },
      dynamicValues: () => ({
        branchId: $('#atc-branchId').val(),
        customerId: $('#atc-customerId').val(),
        type: $('#atc-type').val(),
        observations: $('#atc-observations').val()
      }),

      onSuccessUpdateItemPrice: response => {
        storeCart.loadCart();
      },

      onSuccessLoad: response => $('#cart-table').html(response),

      onSuccessUpdateItemQuantity: response => {},

      onSuccessUpdateRounding: response => {
        const cartTotal = response.cartTotal;
        $('#cartTotal').html(cartTotal);
      },

      onSuccessSaveCart: response => {
        window.open(response.pdf, '_blank');
        location.href = `${BASE_URL}/<?= $rollback; ?>`;
      },

      onSuccessAdd: () => $('#atc-quantity').val('1'),

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
      //onEnter: str => storeCart.cartAction('verify-product-code', str),
      useCleanOnSelect: true
    });

    const customersAutocomplete = new Autocomplete({
      identifier: 'atc-customerId_label',
      source: `${BASE_URL}/data/autocompletes/clientes.php`,
      minLength: 2,
      onSelect: customer => $('#atc-customerId').val(customer.id_cliente)
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

    $('#fd-discount').on('keyup', () => calculateDiscount(calculatePriceWithPercentajeDiscount));
    $('#fd-netPrice').on('keyup', () => calculateDiscount(calculatePriceWithNewPrice));

    $('#btn-clean-cart').on('click', () => storeCart.cleanCart(true, 'Reiniciar carrito', '¿Realmente desea reiniciar el carrito?'));

    $('#details-form').on('submit', e => {
      e.preventDefault();
      storeCart.saveCart();
    });

    $(function() {
      storeCart.loadCart();
      storeCart._initProductsModal();
      searchAutocomplete.initAutocomplete();
      customersAutocomplete.initAutocomplete();
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
</body>

</html>