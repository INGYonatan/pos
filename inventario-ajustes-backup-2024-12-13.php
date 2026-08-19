<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Ajustes de inventario',
  'page_identifier'   => 'inventario-ajustes',
  'tables_config'     => [
    'inventario' => [
      'identifier' => 'inventario'
    ]
  ]
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
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
            <div class="col-12 col-lg-8 col-xl-8">
              <div class="row">
                <div class="col-12">
                  <div class="card adm-card-fh">
                    <form class="card-header bg-transparent">
                      <div class="row">
                        <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                          <div class="form-group">
                            <label class="form-label" for="atc-sucursal">Sucursal</label>
                            <select id="atc-sucursal" class="form-control form-select" name="id_sucursal" required>
                              <?= getBranchOfficesCatalog(getStoreId(), '--Seleccionar--', true); ?>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                          <div class="form-group">
                            <label class="form-label" for="atc-tipo">Ajuste</label>
                            <select id="atc-tipo" class="form-control form-select" name="tipo" required>
                              <option value="<?= TIPO_MOVIMIENTO_INCREMENTO; ?>" selected>Incremento</option>
                              <option value="<?= TIPO_MOVIMIENTO_DECREMENTO; ?>">Decremento</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-6 col-xl-8">
                          <div class="d-flex gap-1 flex-column flex-lg-row">
                            <div>
                              <div class="form-group">
                                <label class="form-label" for="atc-tipo_ajuste">Tipo ajuste</label>
                                <select id="atc-tipo_ajuste" class="form-control form-select" name="tipo_ajuste">
                                  <option value="">--Seleccionar--</option>
                                  <option value="merma">Merma</option>
                                  <option value="perdido">Perdida</option>
                                  <option value="muestra">Muestra</option>
                                  <option value="ajuste">Ajuste</option>
                                </select>
                              </div>
                            </div>

                            <div class="flex-1" style="display: none;">
                              <div class="form-group">
                                <label class="form-label" for="atc-observaciones">Motivo de ajuste</label>
                                <input id="atc-observaciones" class="form-control" name="observaciones" type="text">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                          <div class="form-group">
                            <label class="form-label" for="atc-cantidad">Cantidad</label>
                            <input id="atc-cantidad" class="form-control decimal-input" min="0" value="1" type="text">
                          </div>
                        </div>

                        <div class="col-12 col-md-8 col-lg-9 col-xl-10 ps-md-4">
                          <div class="form-group">
                            <label class="form-label" for="atc-producto">Escribe el nombre del producto</label>
                            <input id="atc-producto" class="form-control" type="text">
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

                      <button id="btn-save-cart" class="btn btn-primary" type="button">
                        <i class="fa fa-check"></i> Realizar ajuste
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-4 col-xl-4">
              <form id="<?= $page_config['tables_config']['inventario']['identifier']; ?>-filters-form" class="row" autocomplete="off">
                <div class="col-md-12 col-lg-12">
                  <div class="card border-primary border adm-card-fh">
                    <div class="card-header">
                      <div class="row">
                        <div class="col-12 col-lg-12">
                          <input id="filter-search" class="form-control" name="search" placeholder="Código, Nombre del producto" type="text">

                          <input id="filter-id-sucursal" name="id_sucursal" value="<?= getStoreId(); ?>" type="hidden">
                        </div>

                        <div class="col-12 col-lg-4 text-end">
                          <div class="dropdown">
                            <div class="btn-group">
                              <?= getFilterActions($page_config['tables_config']['inventario']['identifier']); ?>

                              <?php /* include 'src/components/per-page.php'; */ ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div id="<?= $page_config['tables_config']['inventario']['identifier']; ?>-table" class="card-body fh-table"></div>

                    <!-- CARD LOADING -->
                    <div id="<?= $page_config['tables_config']['inventario']['identifier']; ?>-loading" class="card-loading" style="display: none;">
                      <div class="dimmer active">
                        <div class="spinner-border text-primary m-2" role="status"></div>
                      </div>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $page_config['tables_config']['inventario']['identifier'] . '.php'; ?>

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
      values: {
        //id_sucursal: $('#filter-id-sucursal').val()
      },
      dynamicValues: () => ({
        id_sucursal: $('#filter-id-sucursal').val(),
        tipo: $('#atc-tipo').val(),
        tipo_ajuste: $('#atc-tipo_ajuste').val(),
        observaciones: $('#atc-observaciones').val()
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),
      onSuccessUpdateItemQuantity: response => $(`#new-stock-label-${response.id_producto}`).html(`${response.stock_final}`),
      onSuccessSaveCart: response => {
        load(1, PAGE_CONFIG.tables_config['inventario'].identifier)
        if (response.ticket) window.open(response.ticket, '_blank');
      },
      onSuccessAdd: () => $('#atc-cantidad').val('1'),

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

    $('#btn-save-cart').on('click', () => storeCart.saveCart());

    $('#atc-tipo').on('change', () => storeCart.loadCart());

    $('#atc-sucursal').on('change', function() {
      const idSucursal = $(this).val();

      $('#filter-id-sucursal').val(idSucursal);
      $('#filter-id-sucursal').closest('form').submit();
      storeCart.cartAction('change-branch-office');
    });

    $(function() {
      searchAutocomplete.initAutocomplete();
      storeCart.loadCart();
    });

    $("body").attr("data-sidebar-size", "condensed");

    $("#atc-tipo_ajuste").on("change", function() {
      const tipoAjuste = $(this).val();

      if (tipoAjuste === "ajuste") {
        $("#atc-observaciones").closest(".flex-1").show();
      } else {
        $("#atc-observaciones").closest(".flex-1").hide();
      }
    });
  </script>
</body>

</html>