<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Nueva compra',
  'page_identifier'   => 'nueva-compra'
];

$productos_page_config_id   = 'productos';
$proveedores_page_config_id = 'proveedores';

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

require_once 'data/lib/helpers/purchase-orders.helper.php';
$carrito_ssid = SESSION_CARRITO_NUEVA_COMPRA;

// Precarga especial desde orden de compra si viene ?ocid=...
if (isset($_GET['ocid']) && !empty($_GET['ocid'])) {
  $ocid = $_GET['ocid'];
  global $mysqli, $db_dti;
  $query = "SELECT id_orden_compra, id_sucursal FROM {$db_dti}_ordenes_compra WHERE MD5(id_orden_compra) = '" . $mysqli->real_escape_string($ocid) . "' LIMIT 1";
  $result = $mysqli->query($query);
  if ($result && $row = $result->fetch_assoc()) {
    $id_orden_compra = $row['id_orden_compra'];
    $id_sucursal = $row['id_sucursal'];
    $orden = purchase_order_get_by_id($id_orden_compra);
    $preload_form_data = null;
    if ($orden) {
      // Precargar datos del formulario
      $preload_form_data = [
        'id_sucursal'      => $orden->branch_id,
        'folio_documento'  => $orden->document_folio,
        'fecha_documento'  => $orden->document_date_format,
        'metodo_pago'      => $orden->payment_method,
        'forma_pago'       => $orden->payment_form,
        'id_proveedor'     => $orden->supplier_id,
        'observaciones'    => $orden->observations
      ];
      if (!empty($orden->list)) {
        $carrito = [];
        foreach ($orden->list as $producto) {
          $data_producto = getBranchOfficeProductData($id_sucursal, $producto->id);
          // Dar prioridad a los datos de la orden, solo complementar con inventario
          $carrito[$producto->id] = [
            'id_producto'       => $producto->id,
            'codigo'            => $producto->code ?? ($data_producto['codigo'] ?? ''),
            'nombre_producto'   => $producto->name ?? ($data_producto['nombre_producto'] ?? ''),
            'stock'             => $producto->stock ?? ($data_producto['stock'] ?? 0),
            'cantidad'          => $producto->quantity,
            'aplica_iva'        => $producto->have_iva ?? ($data_producto['aplica_iva'] == 'si' ? true : false),
            'iva_porcentaje'    => isset($producto->iva) ? 16 : (($data_producto && $data_producto['aplica_iva'] == 'si') ? 16 : 0),
            'aplica_ieps'       => $producto->have_ieps ?? (($data_producto['aplica_ieps'] ?? 'no') == 'si' ? true : false),
            'ieps_porcentaje'   => isset($producto->ieps_percentage) ? $producto->ieps_percentage : doubleval($data_producto['ieps_porcentaje'] ?? 0),
            'descuento'         => $producto->discount,
            'limite_descuento'  => $producto->limitDiscount ?? ($data_producto['limite_descuento'] ?? 0),
            'precio_original'   => $producto->original_price,
            'precio_costo'      => $producto->cost_price,
            'unidad_entrada'    => $producto->inputUnit ?? ($data_producto['unidad_entrada'] ?? ''),
            'numero_piezas'     => $producto->piecesNumber ?? ($data_producto['numero_piezas'] ?? 0),
            'id_tipo'           => $producto->id_tipo ?? ($data_producto['id_tipo'] ?? null),
            'tipo'              => ($data_producto['tipo'] ?? ''),
            'requiere_numero_serie' => $data_producto['requiere_numero_serie'],
            'serial_numbers'    => []
          ];
        }
        $_SESSION[$carrito_ssid] = $carrito;
      }
    }
  }
} else {
  unset($_SESSION[$carrito_ssid]);
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
            "pageTitle"       => "Nueva compra",
            "pageDescription" => "Crea una nueva compra para tu sucursal"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-9 col-xl-9">
              <div class="card  card-border-top border-top-primary">
                <form class="card-header bg-transparent">
                  <div class="row">
                    <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                      <div class="form-group">
                        <label class="form-label text-dark" for="atc-cantidad">Cantidad</label>
                        <input id="atc-cantidad" class="form-control form-control-lg decimal-input" min="0" value="1" type="text">
                      </div>
                    </div>

                    <div class="col-12 <?= checkModuleActionPermission('productos', 'agregar') ? 'col-md-4 col-lg-6 col-xl-8 ps-md-4' : 'col-md-8 col-lg-9 col-xl-10 ps-md-4' ?>">
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

                    <?php if (checkModuleActionPermission('productos', 'agregar')) : ?>
                      <div class="col-12 col-md-4 col-lg-3 col-xl-2">
                        <div class="form-group">
                          <label class="form-label" for="">Producto</label>
                          <button id="btn-add-product" class="btn btn-lg btn-primary btn-block w-100" data-bs-toggle="modal" data-bs-target="#<?= $productos_page_config_id; ?>-modal" type="button">
                            <i class="fa fa-plus"></i> Nuevo
                          </button>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>
                </form>

                <div id="tabla-carrito" class="card-body"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>

                <div class="card-footer text-end">
                  <button id="btn-clean-cart" class="btn btn-danger" type="button">
                    <i class="fa fa-trash"></i> Vacíar
                  </button>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-3 col-xl-3">
              <form id="<?= $page_config['page_identifier']; ?>-form-data" class="card " autocomplete="off">
                <script>
                  // Precargar datos del formulario si vienen de la orden
                  <?php if (isset($preload_form_data) && $preload_form_data): ?>
                    document.addEventListener('DOMContentLoaded', function() {
                      var preload = <?= json_encode($preload_form_data); ?>;
                      if (preload.id_sucursal) $('#atc-id_sucursal').val(preload.id_sucursal).trigger('change');
                      if (preload.folio_documento) $('#folio_documento').val(preload.folio_documento);
                      if (preload.fecha_documento) $('#fecha_documento').val(preload.fecha_documento);
                      if (preload.metodo_pago) $('#metodo_pago').val(preload.metodo_pago);
                      if (preload.forma_pago) $('#forma_pago').val(preload.forma_pago);
                      if (preload.id_proveedor) $('#id_proveedor').val(preload.id_proveedor);
                      if (preload.observaciones) $('#observaciones').val(preload.observaciones);
                    });
                  <?php endif; ?>
                </script>
                <div class="card-header bg-primary">
                  <h3 class="header-title text-dark m-0">Detalles de la compra</h3>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-md-8">
                      <div class="form-group">
                        <label class="form-label" for="atc-id_sucursal">Sucursal<span class="text-danger">*</span></label>
                        <select id="atc-id_sucursal" class="form-select form-select" data-modalProductosBranchId required>
                          <?= getBranchOfficesCatalog(getStoreId(), '--Seleccionar--', true, false); ?>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="folio_documento">Folio del documento<span class="text-danger">*</span></label>
                        <input id="folio_documento" class="form-control" name="folio_documento" type="text" required>
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="fecha_documento">Fecha del documento<span class="text-danger">*</span></label>
                        <div class="input-group">
                          <input id="fecha_documento" class="form-control datepicker" name="fecha_documento" value="<?= date('d-m-Y'); ?>" type="text" required>

                          <div class="input-group-text">
                            <i class="fa fa-calendar"></i>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="metodo_pago">Método de pago<span class="text-danger">*</span></label>
                        <select id="metodo_pago" class="form-control form-select" name="metodo_pago" required>
                          <option value="De contado" selected>De contado</option>
                          <option value="Credito">Credito</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <div class="form-group">
                        <label class="form-label" for="forma_pago">Forma de pago<span class="text-danger">*</span></label>
                        <select id="forma_pago" class="form-control form-select" name="forma_pago" required>
                          <option value="Efectivo" selected>Efectivo</option>
                          <option value="Cheque">Cheque</option>
                          <option value="Transferencia">Transferencia</option>
                          <option value="Tarjeta de débito">Tarjeta de débito</option>
                          <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-md-9">
                      <div class="form-group">
                        <label class="form-label" for="id_proveedor">Proveedor<span class="text-danger">*</span></label>

                        <div class="input-group">
                          <select id="id_proveedor" class="form-control form-select supplier-catalog" name="id_proveedor" required>
                            <?= getSupplierCatalog(); ?>
                          </select>

                          <button id="btn-add-supplier" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#<?= $proveedores_page_config_id; ?>-modal" type="button">
                            <i class="fa fa-plus"></i> Nuevo
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-laber" for="observaciones">Observaciones</label>
                        <textarea id="observaciones" class="form-control" name="observaciones" rows="3"></textarea>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-footer bg-transparent border-0 py-0 text-end" style="display: none;">
                  <div class="d-flex w-100 flex-column align-items-center justify-content-center flex-md-row align-items-md-start justify-content-md-end gap-2 mt-2 mb-2">
                    <div class="text-right">
                      <h3 id="carrito-total" class="m-0">Total: $0.00 MXN</h3>
                    </div>
                  </div>
                </div>

                <div class="card-footer bg-transparent">
                  <div class="row">
                    <div class="col-12 bg-light py-2">
                      <button class="btn btn-lg btn-block btn-primary w-100" type="submit">Realizar compra</button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/productos.php'; ?>
      <?php include 'src/modals/proveedores.php'; ?>
      <?php include 'src/modals/carrito-numeros-serie.php'; ?>

      <?php
      $carrito_modal_productos_branch_id = getStoreId();
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

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const getQuantity = () => $('#atc-cantidad').val();

    const storeCart = new Cart({
      identifier: PAGE_CONFIG.page_identifier,
      source: PAGE_CONFIG.page_identifier,
      values: {},
      dynamicValues: () => ({
        id_sucursal: $('#atc-id_sucursal').val(),
        folio_documento: $('#folio_documento').val(),
        fecha_documento: $('#fecha_documento').val(),
        metodo_pago: $('#metodo_pago').val(),
        forma_pago: $('#forma_pago').val(),
        id_proveedor: $('#id_proveedor').val(),
        observaciones: $('#observaciones').val(),

        <?php if (isset($_GET['ocid']) && !empty($_GET['ocid'])): ?>
          id_orden_compra: '<?= $orden->id; ?>'
        <?php endif; ?>
      }),

      onSuccessLoad: response => $('#tabla-carrito').html(response),

      onSuccessUpdateItemQuantity: response => {
        $(`#cost-price-label-${response.id_producto}`).html(`${response.precio_costo_final}`);
        $(`#carrito-total`).html(`${response.total}`);
      },

      onSuccessUpdateItemPrice: response => {
        $(`#cost-price-label-${response.id_producto}`).html(`${response.precio_costo_final}`);
        $(`#carrito-total`).html(`${response.total}`);
      },

      onSuccessSaveCart: response => {
        $(`#${PAGE_CONFIG.page_identifier}-form-data`).trigger('reset');
        window.history.replaceState({}, document.title, window.location.pathname);
        if (response.ticket) window.open(response.ticket, '_blank');
        setTimeout(() => {
          location.reload();
        }, 200);
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

    $(document).on('keyup', '.price-input', function(e) {
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

    $('#atc-tipo_ajuste').on('change', () => storeCart.loadCart());

    $('#atc-sucursal').on('change', function() {
      const idSucursal = $(this).val();

      $('#filter-id-sucursal').val(idSucursal);
      $('#filter-id-sucursal').closest('form').submit();
      storeCart.cartAction('change-branch-office');
    });

    $('#btn-add-product').on('click', () => {
      $(`#<?= $productos_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $productos_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $productos_page_config_id; ?>');
    });

    $('#btn-add-supplier').on('click', () => {
      $(`#<?= $proveedores_page_config_id; ?>-form-data`).trigger('reset');
      $(`#<?= $proveedores_page_config_id; ?>-form-data [name="action"]`).val('add-<?= $proveedores_page_config_id; ?>');
    });

    const updaterow = ({
      id,
      quantity,
      price,
      discount
    }) => {
      // Validar que los campos no estén vacíos
      if (quantity === '' || price === '' || discount === '') {
        return;
      }

      // Validar que la cantidad sea mayor a 0

      if (quantity == 0) return;

      callEndpoint({
        place: PAGE_CONFIG.page_identifier,
        parameters: {
          action: 'cart-update-row',
          id,
          quantity,
          price,
          discount
        },
      }).then(response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        if (response.status === "success") storeCart.loadCart();
      })
    }

    $(function() {
      searchAutocomplete.initAutocomplete();
      storeCart.loadCart();
      storeCart._initProductsModal();
      storeCart._initAddSerialNumber();
    });
  </script>

  <script>
    $("body").attr("data-sidebar-size", "condensed");
  </script>
</body>

</html>