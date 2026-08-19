<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Nueva solicitud de traspaso',
  'page_identifier'   => 'inventario-solicitudes-de-traspasos',
];

checkModuleActionPermission($page_config['page_identifier'], 'agregar', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$cartSSID             = SESSION_CARRITO_SOLICITUD_TRASPASO;

$destinationBranchId  = /* $_GET["destination"] ? cleanStr($_GET["destination"]) : */ getStoreId();

if ($IS_ADMIN)  $destinationBranchId = $_GET["destination"] ? cleanStr($_GET["destination"]) : getStoreId();

if (!$IS_ADMIN) {
  $destinationBranchId   = getSessionBranchOfficeId();
  $destinationBranchData = getBranchOfficeData($destinationBranchId);
}

$originBranchId  = $_GET["origin"];
$canTransfer          = $originBranchId && $destinationBranchId ? true : false;

unset($_SESSION[$cartSSID]);
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
              <div class="card">
                <div class="card-header bg-transparent">
                  <h4 class="header-title text-dark m-0">Detalles de la solicitud</h4>
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
                        <div class="form-label">Sucursal a solicitar<span class="text-danger">*</span></div>

                        <div class="input-group">
                          <select id="fd-originBranchId" class="form-control form-select" name="originBranchId" data-modalProductosBranchId required>
                            <?= getBranchOfficesCatalog($originBranchId, "--Seleccionar--", true, true, $destinationBranchId); ?>
                          </select>

                          <div class="input-group-text" style="display: none;">
                            <i class="fas fa-arrow-right"></i>
                          </div>

                          <select id="fd-destinationBranchId" class="form-control form-select" name="destinationBranchId" required style="display: none;">
                            <?php /* if ($IS_ADMIN) : ?>
                              <?= getBranchOfficesCatalog($destinationBranchId, "--Destino--", true, false); ?>
                            <?php endif; */ ?>

                            <?php /* if (!$IS_ADMIN) : */ ?>
                            <option value="<?= $destinationBranchId; ?>">
                              <?= $destinationBranchData['nombre_sucursal']; ?>
                            </option>
                            <?php /* endif; */ ?>
                          </select>
                        </div>
                      </label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <label class="form-group">
                        <div class="form-label">Observaciones</div>
                        <textarea id="fd-observations" class="form-control" name="observations" rows="5"></textarea>
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
                          <i class="fas fa-exclamation-triangle"></i> Seleccione la sucursal a la que desea solicitar el traspaso para agregar productos a la solicitud.
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
                        Realizar solicitud
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
    // Sucursal destino
    $("#fd-destinationBranchId").on("change", function() {
      const branchId = $(this).val();

      updateUrlParams("destination", branchId);
      updateUrlParams("origin", null);

      location.reload();
    });

    // Sucursal origen
    $("#fd-originBranchId").on("change", function() {
      const branchId = $(this).val();

      updateUrlParams("origin", branchId);

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
          observations: $("#fd-observations").val()
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

          setTimeout(() => {
            location.reload();
          }, 1000);
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
      $('#btn-transfer').on('click', () => storeCart.saveCart('Solicitud de Traspaso', '¿Realmente desea realizar la solicitud de traspaso?'));

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