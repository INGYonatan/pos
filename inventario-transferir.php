<?php
include 'inc/session.inc.php';
require_once __DIR__ . '/data/lib/shopping-cart.php';
require_once __DIR__ . '/data/lib/models/solicitudes-traspasos-productos.model.php';

$page_config = [
  'page_title'        => 'Traspasos',
  'page_identifier'   => 'inventario-transferir',
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$cartSSID             = SESSION_CARRITO_TRANSFERIR_INVENTARIO;

$suid                 = isset($_GET["suid"]) ? cleanStr($_GET["suid"]) : "";
$transferRequestData  = null;
$transferRequestNotes = "";

$originBranchId       = $_GET["origin"] ? cleanStr($_GET["origin"]) : getStoreId();

if ($IS_ADMIN)  $originBranchId = $_GET["origin"] ? cleanStr($_GET["origin"]) : getStoreId();

if (!$IS_ADMIN) {
  $originBranchId   = getSessionBranchOfficeId();
  $originBranchData = getBranchOfficeData($originBranchId);
}

$destinationBranchId  = $_GET["destination"];

if (!empty($suid)) {
  $query = "SELECT * FROM paal_solicitud_transferencias WHERE MD5(id_solicitud_transferencia) = ? LIMIT 1";
  $stmt  = $mysqli->prepare($query);

  if ($stmt) {
    $stmt->bind_param("s", $suid);
    $stmt->execute();

    $result = $stmt->get_result();
    $data   = $result->fetch_assoc();

    if ($data) {
      $transferRequestData  = $data;
      $transferRequestNotes = $data["notas"] ?? "";

      $originBranchId       = $data["id_sucursal_origen"];
      $destinationBranchId  = $data["id_sucursal_destino"];
    }
  }
}

$canTransfer          = $originBranchId && $destinationBranchId ? true : false;

if (!empty($suid) && $transferRequestData && $canTransfer) {
  $productsModel  = new TransferRequestProductsModel();
  $productsResult = $productsModel->getByTransferRequestId($transferRequestData["id_solicitud_transferencia"]);

  $cartSession = new stdClass();
  $cartSession->list = [];

  if ($productsResult->status == "success") {
    foreach ($productsResult->data->rows as $item) {
      $productId = $item->getProductId();
      $quantity  = $item->getRequestedQuantity();

      if ($productId <= 0 || $quantity <= 0) continue;

      $cart = new ShoppingCart(
        $destinationBranchId,
        $productId,
        $cartSession,
        false
      );

      $list = $cartSession->list;

      if (isset($list[$productId])) {
        $cart->increase_product_quantity($quantity);
      }

      if (!isset($list[$productId])) $cart->add_product($quantity);

      if ($cart->get_alert()->status == "success") {
        $cartSession = $cart->get_cart();
      }
    }
  }

  $_SESSION[$cartSSID] = $cartSession;
} else {
  unset($_SESSION[$cartSSID]);
}
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
            "pageTitle"       => "Nueva transferencia",
            "pageDescription" => "Transfiere el stock de tus sucursales"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card">
                <div class="card-header bg-transparent">
                  <h4 class="header-title text-dark m-0">Detalles de la transferencia</h4>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-6">
                      <label class="form-group">
                        <div class="form-label">Fecha<span class="text-danger">*</span></div>
                        <input id="fd-date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>" type="date" readonly required>
                      </label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <label class="form-group">
                        <div class="form-label">Sucursal<span class="text-danger">*</span></div>

                        <div class="input-group">
                          <select id="fd-originBranchId" class="form-control form-select" name="originBranchId" data-modalProductosBranchId required>
                            <?php if ($IS_ADMIN) : ?>
                              <?= getBranchOfficesCatalog($originBranchId, "--Origen--", true, false); ?>
                            <?php endif; ?>

                            <?php if (!$IS_ADMIN) : ?>
                              <option value="<?= $originBranchId; ?>">
                                <?= $originBranchData['nombre_sucursal']; ?>
                              </option>
                            <?php endif; ?>
                          </select>

                          <div class="input-group-text">
                            <i class="fas fa-arrow-right"></i>
                          </div>

                          <select id="fd-destinationBranchId" class="form-control form-select" name="destinationBranchId" required>
                            <?= getBranchOfficesCatalog($destinationBranchId, "--Destino--", true, true, $originBranchId); ?>
                          </select>
                        </div>
                      </label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <label class="form-group">
                        <div class="form-label">Observaciones</div>
                        <textarea id="fd-observations" class="form-control" name="observations" rows="5"><?= htmlspecialchars($transferRequestNotes); ?></textarea>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-8 col-xl-8">
              <?php if (!$canTransfer) : ?>
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="alert alert-info">
                          <i class="fas fa-exclamation-triangle"></i> Seleccione las sucursales de origen y destino
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($canTransfer) : ?>
                <div class="card">
                  <div class="card-header bg-transparent">
                    <div class="row">
                      <div class="col-4 col-md-2">
                        <label class="form-group mb-1">
                          <div class="form-label">Cantidad</div>
                          <input id="fd-quantity" class="form-control" name="quantity" min="1" value="1" type="number">
                        </label>
                      </div>

                      <div class="col-8 col-md-10">
                        <label class="form-group mb-1">
                          <div class="form-label">Buscar producto<span class="text-danger">*</span></div>
                          <div class="input-group">
                            <input id="fd-search" class="form-control" name="search" value="" type="text" placeholder="Nombre, Código...">

                            <button class="input-group-text" data-bs-toggle="modal" data-bs-target="#carrito-modal-productos-modal" type="button">
                              <i class="fas fa-search"></i>
                            </button>
                          </div>
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="card-body">
                    <div id="table-cart"></div>
                  </div>

                  <div class="card-footer bg-transparent">
                    <div class="d-flex flex-column flex-md-row align-items-center justify-content-between">
                      <button id="btn-clean-cart" class="btn text-danger" type="button">
                        <i class="fa fa-trash"></i> Vaciar carrito
                      </button>

                      <button id="btn-transfer" class="btn btn-primary" type="button">
                        <i class="fa fa-check me-1"></i>
                        Realizar transferencia
                      </button>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <?php
      /**
       * MODALS
       */
      $carrito_modal_productos_branch_id = $originBranchId;
      include 'src/modals/carrito-modal-productos.php';
      include 'src/modals/carrito-numeros-serie.php';
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

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <!-- CART -->
  <script src="<?= BASE_URL; ?>/src/plugins/cart/main.js"></script>

  <!-- MULTIDATATABLE -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script>
    $("body").attr("data-sidebar-size", "condensed");

    // Sucursal origen
    $("#fd-originBranchId").on("change", function() {
      const branchId = $(this).val();

      updateUrlParams("origin", branchId);
      updateUrlParams("destination", null);

      location.reload();
    });

    // Sucursal destino
    $("#fd-destinationBranchId").on("change", function() {
      const branchId = $(this).val();

      updateUrlParams("destination", branchId);

      location.reload();
    });

    <?php if ($canTransfer) : ?>
      // Cart
      const storeCart = new Cart({
        identifier: PAGE_CONFIG.page_identifier,
        source: PAGE_CONFIG.page_identifier,

        withSerialNumbersCatalog: true,

        dynamicValues: () => ({
          date: $("#fd-date").val(),
          originBranchId: $("#fd-originBranchId").val(),
          destinationBranchId: $("#fd-destinationBranchId").val(),
          observations: $("#fd-observations").val(),

          <?php if (!empty($suid) && $transferRequestData && $canTransfer) : ?>
            transferRequestId: <?= $transferRequestData["id_solicitud_transferencia"]; ?>
          <?php endif; ?>
        }),

        onSuccessLoad: response => $("#table-cart").html(response),

        onSuccess: response => {
          if (response.toastMessage) showSweetToast({
            icon: response.status,
            message: response.toastMessage
          });

          storeCart.loadCart();
        },

        onSuccessSaveCart: response => {
          if (response.ticket) window.open(response.ticket, '_blank');

          <?php if ($suid) : ?>
            // Remover el suid de la URL
            updateUrlParams("suid", null);
          <?php endif; ?>


          <?php if (!$_GET["redirect"]) : ?>
            setTimeout(() => {
              location.reload();
            }, 1000);
          <?php endif; ?>

          <?php if ($_GET["redirect"]) : ?>
            setTimeout(() => {
              window.location.href = "<?= BASE_URL; ?>/<?= $_GET["redirect"]; ?>";
            }, 1000);
          <?php endif; ?>
        },

        onError: response => {
          if (response.toastMessage) showSweetToast({
            icon: response.status,
            message: response.toastMessage
          });

          storeCart.loadCart();
        }
      });

      // iniciar el modal de productos
      storeCart._initProductsModal();

      // Inicializar la tabla de productos
      storeCart.loadCart();

      // Inicializar la tabla de numeros de serie
      storeCart._initAddSerialNumber();

      // Vaciar el carrito
      $('#btn-clean-cart').on('click', () => storeCart.cleanCart());

      // Submit
      $('#btn-transfer').on('click', () => storeCart.saveCart('Traspaso', '¿Realmente desea realizar el traspaso?'));

      // Autocompletado de productos
      const searchAutocomplete = new Autocomplete({
        identifier: "fd-search",
        source: `${BASE_URL}/data/autocompletes/productos.php`,
        minLength: 2,
        onSelect: product => storeCart.addItem(product.id_producto, $("#fd-quantity").val()),
        useCleanOnSelect: true
      });

      searchAutocomplete.initAutocomplete();
    <?php endif; ?>
  </script>

  <!-- VALIDATE.INIT.JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>