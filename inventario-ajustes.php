<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Ajustes de inventario',
  'page_identifier'   => 'inventario-ajustes',
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);


$cartSSID       = SESSION_CARRITO_AJUSTES_INVENTARIO;
$branchId       = $_GET["branchId"];
$adjustment     = $_GET["adjustment"];
$adjustmentType = $_GET["adjustmentType"];
$canInit        = $branchId && $adjustment && $adjustmentType ? true : false;

unset($_SESSION[$cartSSID]);

$pageId = $page_config['page_identifier'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">

  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/select2/styles.css">
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
            "pageTitle"       => "Ajustes de inventario",
            "pageDescription" => "Ajusta el stock de tu sucursal"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-4 col-xl-4">
              <div class="card">
                <div class="card-header bg-transparent">
                  <h4 class="header-title text-dark m-0">Detalles del ajuste</h4>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-6">
                      <label class="form-group">
                        <div class="form-label">Fecha<span class="text-danger">*</span></div>
                        <input id="fd-date" class="form-control" name="date" value="<?= date('Y-m-d'); ?>" type="date" readonly required>
                      </label>
                    </div>

                    <div class="col-6">
                      <label class="form-group">
                        <div class="form-label">Sucursal<span class="text-danger">*</span></div>

                        <select id="fd-branchId" class="form-control form-select with-reload" name="branchId" required>
                          <?= getBranchOfficesCatalog($branchId, "--Seleccionar--", true, true); ?>
                        </select>
                      </label>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-6">
                      <label class="form-group">
                        <div class="form-label">Ajuste<span class="text-danger">*</span></div>

                        <select id="fd-adjustment" class="form-control form-select with-reload" name="adjustment" required>
                          <option value="">--Seleccionar--</option>
                          <option <?= $adjustment == TIPO_MOVIMIENTO_INCREMENTO ? "selected" : ""; ?> value="<?= TIPO_MOVIMIENTO_INCREMENTO; ?>">Incremento</option>
                          <option <?= $adjustment == TIPO_MOVIMIENTO_DECREMENTO ? "selected" : ""; ?> value="<?= TIPO_MOVIMIENTO_DECREMENTO; ?>">Decremento</option>
                        </select>
                      </label>
                    </div>

                    <?php if ($adjustment): ?>
                      <div class="col-6">
                        <label class="form-group">
                          <div class="form-label">Tipo de ajuste<span class="text-danger">*</span></div>

                          <select id="fd-adjustmentType" class="form-control form-select with-reload" name="adjustmentType" required>
                            <option value="">--Seleccionar--</option>

                            <option <?= $adjustmentType == "merma" ? "selected" : ""; ?> value="merma">Merma</option>

                            <?php if ($adjustment == "decremento") : ?>
                              <option <?= $adjustmentType == "perdida" ? "selected" : ""; ?> value="perdida">Perdida</option>
                            <?php endif; ?>

                            <?php if ($adjustment == "incremento") : ?>
                              <option <?= $adjustmentType == "muestra" ? "selected" : ""; ?> value="muestra">Muestra</option>
                            <?php endif; ?>

                            <option <?= $adjustmentType == "ajuste" ? "selected" : ""; ?> value="ajuste">Ajuste</option>
                          </select>
                        </label>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if ($adjustmentType == "ajuste") : ?>
                    <div class="row">
                      <div class="col-12">
                        <label class="form-group">
                          <div class="form-label">Motivo del ajuste</div>
                          <textarea id="fd-observations" class="form-control" name="observations" rows="5"></textarea>
                        </label>
                      </div>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-8 col-xl-8">
              <?php if (!$canInit) : ?>
                <div class="card">
                  <div class="card-body">
                    <div class="row">
                      <div class="col-12">
                        <div class="alert alert-info">
                          <i class="fas fa-exclamation-triangle"></i> Completa los campos requeridos del formulario
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

              <?php if ($canInit) : ?>
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
                        Realizar ajuste
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
      $carrito_modal_productos_branch_id          = $branchId;
      $carrito_modal_productos_use_limit_quantity = "no";

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

  <!-- SELECT2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- AUTOCOMPLETEO -->
  <script src="<?= BASE_URL; ?>/src/plugins/autocomplete/main.js"></script>

  <!-- CART -->
  <script src="<?= BASE_URL; ?>/src/plugins/cart/main.js"></script>

  <!-- MULTIDATATABLE -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script>
    // Campos con reload
    $(".with-reload").on("change", function() {
      showPageLoading();

      const name = $(this).attr("name");
      const value = $(this).val();

      updateUrlParams(name, value);
      location.reload();
    });

    <?php if ($canInit) : ?>
      const storeCart = new Cart({
        identifier: PAGE_CONFIG.page_identifier,
        source: PAGE_CONFIG.page_identifier,
        withSerialNumbersCatalog: $("#fd-adjustment").val() == "decremento" ? true : false,

        dynamicValues: () => ({
          date: $("#fd-date").val(),
          branchId: $("#fd-branchId").val(),
          adjustment: $("#fd-adjustment").val(),
          adjustmentType: $("#fd-adjustmentType").val(),
          observations: $("#fd-observations").length ? $("#fd-observations").val() : ""
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

          //storeCart.loadCart();
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
      $('#btn-transfer').on('click', () => storeCart.saveCart('Ajuste de inventario', '¿Realmente desea realizar el ajuste?'));

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