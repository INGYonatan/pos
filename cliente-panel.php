<?php
include 'inc/session.inc.php';
require 'data/lib/helpers/customers.helper.php';
include 'data/lib/helpers/catalogs.helper.php';

$customer_id  = cleanStr($_GET['uid']);
$url_ref      = cleanStr($_GET['ref']);
$customer     = customer_get_by_id($customer_id);

if (!$customer) :
  closeSession();
  die;
endif;

$pc_tables_config = [
  'direcciones' => [
    'identifier' => 'cliente-direcciones'
  ],
  'cotizaciones-abiertas' => [
    'identifier' => 'cotizaciones-abiertas'
  ],
  'cotizaciones-cerradas' => [
    'identifier' => 'cotizaciones-cerradas'
  ],
  'ventas' => [
    'identifier' => 'ventas'
  ]
];

if ($customer->type === 'moral') $pc_tables_config['sucursales'] = [
  'identifier' => 'cliente-sucursales'
];

$page_config = [
  'page_title'        => 'Cliente | ' . $customer->name,
  'page_identifier'   => 'cliente-panel',
  'tables_config'     => $pc_tables_config
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

<body class="loading" data-sidebar-size="condensed">
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
            <div class="col-12 col-lg-3">
              <div class="card adm-card-fh">
                <div class="card-body text-center px-0">
                  <div class="avatar-xl mx-auto mt-1">
                    <div class="avatar-title bg-light rounded-circle">
                      <i class="mdi mdi-account h1 m-0 text-body"></i>
                    </div>
                  </div>

                  <h4 class="mt-3 mb-0"><?= $customer->name; ?></h4>
                  <?php if ($customer->commercialName) : ?>
                    <p class="text-muted"><?= $customer->commercialName; ?></p>
                  <?php endif; ?>

                  <div class="text-start mt-3">
                    <table class="table table-borderless table-sm">
                      <tbody>
                        <tr>
                          <th scope="row">Teléfono:</th>
                          <td class="text-muted"><?= formatPhoneNumber($customer->phone); ?></td>
                        </tr>

                        <tr>
                          <th scope="row">Correo:</th>
                          <td class="text-muted"><?= $customer->email; ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-9">
              <div class="card adm-card-fh">
                <div class="card-header bg-white">
                  <ul id="cliente-panel-header" class="nav nav-pills navtab-bg">
                    <li class="nav-item">
                      <a class="nav-link ms-0 <?= $url_ref === 'datos-generales' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-datos-generales" data-place="datos-generales">
                        Datos generales
                      </a>
                    </li>

                    <li class="nav-item">
                      <a class="nav-link ms-0 <?= $url_ref === 'direcciones' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-direcciones" data-place="direcciones">
                        Direcciones
                      </a>
                    </li>

                    <?php if ($customer->type === 'moral') : ?>
                      <li class="nav-item">
                        <a class="nav-link ms-0 <?= $url_ref === 'sucursales' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-sucursales" data-place="sucursales">
                          Sucursales
                        </a>
                      </li>
                    <?php endif; ?>

                    <?php /* 
                    <li class="nav-item">
                      <a class="nav-link ms-0 <?= $url_ref === 'cotizaciones-abiertas' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-cotizaciones-abiertas" data-place="cotizaciones-abiertas">
                        Cotizaciones abiertas
                      </a>
                    </li>

                    <li class="nav-item">
                      <a class="nav-link ms-0 <?= $url_ref === 'cotizaciones-cerradas' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-cotizaciones-cerradas" data-place="cotizaciones-cerradas">
                        Cotizaciones cerradas
                      </a>
                    </li>

                    <li class="nav-item">
                      <a class="nav-link ms-0 <?= $url_ref === 'ventas' ? 'active' : ''; ?>" data-bs-toggle="tab" aria-expanded="true" href="#tab-ventas" data-place="ventas">
                        Ventas
                      </a>
                    </li>
                    */ ?>
                  </ul>
                </div>

                <div class="card-body p-0">
                  <div class="tab-content p-0">
                    <div id="tab-datos-generales" class="tab-pane card-body <?= $url_ref === 'datos-generales' ? 'show active' : ''; ?>">
                      <form id="clientes-form-data" class="form-validate" autocomplete="off">
                        <div class="row">
                          <div class="col-12">
                            <h3 class="header-title">Datos de cliente</h3>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12">
                            <div class="form-group">
                              <label class="form-label" for="nombre_completo">Nombre completo<span class="text-danger">*</span></label>
                              <input id="nombre_completo" class="form-control" name="nombre_completo" value="<?= $customer->name; ?>" type="text" required>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12">
                            <div class="form-group">
                              <label class="form-label" for="nombre_comercial">Nombre comercial</label>
                              <input id="nombre_comercial" class="form-control" name="nombre_comercial" value="<?= $customer->commercialName; ?>" type="text" required>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12 col-lg-6">
                            <div class="form-group">
                              <label class="form-label" for="fd-limite_credito">Límite de crédito<span class="text-danger">*</span></label>
                              <input id="fd-limite_credito" class="form-control number-input" name="limite_credito" value="<?= $customer->creditLimit; ?>" type="text" required>
                            </div>
                          </div>

                          <div class="col-12 col-lg-6">
                            <div class="form-group">
                              <label class="form-label" for="fd-limite_credito_plazo">Plazo de crédito (días)<span class="text-danger">*</span></label>
                              <input id="fd-limite_credito_plazo" class="form-control number-input" name="limite_credito_plazo" value="<?= $customer->creditLimitTerm; ?>" step="1" type="number" required>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12">
                            <h3 class="header-title">Datos de contacto</h3>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12 col-lg-6">
                            <div class="form-group">
                              <label class="form-label" for="correo">Correo</label>
                              <input id="correo" class="form-control" name="correo" value="<?= $customer->email; ?>" type="email">
                            </div>
                          </div>

                          <div class="col-12 col-lg-6">
                            <div class="form-group">
                              <label class="form-label" for="telefono">Teléfono</label>
                              <input id="telefono" class="form-control number-input" size="10" name="telefono" value="<?= $customer->phone; ?>" type="number">
                            </div>
                          </div>
                        </div>

                        <div class="row mb-2">
                          <div class="col-12">
                            <input id="requiere_factura" class="check-with-content" name="requiere_factura" value="si" type="checkbox" <?= $customer->requireInvoice ? 'checked' : ''; ?>>
                            <label for="requiere_factura" class="form-label label-check">¿Requiere factura?</label>

                            <div class="content-check">
                              <div class="row">
                                <div class="col-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="form-label" for="razon_social">Razón social</label>
                                    <input id="razon_social" class="form-control" name="razon_social" value="<?= $customer->businessName; ?>" type="text" required>
                                  </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="form-label" for="id_regimen_fiscal">Tipo</label>
                                    <select id="tipo" class="form-control form-select" name="tipo" required>
                                      <option value="">--Seleccionar--</option>
                                      <option <?= $customer->type === 'fisica' ? 'selected' : ''; ?> value="fisica">Física</option>
                                      <option <?= $customer->type === 'moral' ? 'selected' : ''; ?> value="moral">Moral</option>
                                    </select>
                                  </div>
                                </div>
                              </div>

                              <div class="row">
                                <div class="col-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="form-label" for="rfc">RFC</label>
                                    <input id="rfc" class="form-control" name="rfc" type="text" value="<?= $customer->rfc; ?>" required>
                                  </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="form-label" for="id_regimen_fiscal">Regimen fiscal</label>
                                    <select id="id_regimen_fiscal" class="form-control form-select" name="id_regimen_fiscal" required>
                                      <?= catalog_get_tax_regime($customer->taxRegimeId); ?>
                                    </select>
                                  </div>
                                </div>
                              </div>

                              <div class="row">
                                <div class="col-12 col-lg-6">
                                  <div class="form-group">
                                    <label class="form-label" for="domicilio_fiscal">Domicilio fiscal (CP)</label>
                                    <input id="domicilio_fiscal" class="form-control" name="domicilio_fiscal" value="<?= $customer->taxResidence; ?>" type="text" required>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <input name="uid" value="<?= $customer->id; ?>" type="hidden">
                        <input name="action" value="edit-clientes" type="hidden">
                        <input name="place" value="clientes" type="hidden">

                        <div class="position-absolute bottom-0 pb-2" style="right: 0.5rem;">
                          <button class="btn btn-primary" type="submit">Guardar cambios</button>
                        </div>
                      </form>
                    </div>

                    <div id="tab-direcciones" class="tab-pane <?= $url_ref === 'direcciones' ? 'show active' : ''; ?>">
                      <form id="<?= $page_config['tables_config']['direcciones']['identifier']; ?>-filters-form" autocomplete="off">
                        <div class="card-header bg-white position-sticky top-0" style="z-index: 1;">
                          <div class="row">
                            <div class="col-12 col-lg-8">
                              <div class="row">
                                <div class="col-12 col-md-6 col-lg-5 mb-2 mb-lg-0">
                                  <div class="form-group">
                                    <label class="form-label" for="direcciones-filter-search">Buscar aquí</label>
                                    <input id="direcciones-filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                                  </div>
                                </div>

                                <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">
                              </div>
                            </div>

                            <div class="col-12 col-lg-4 text-center text-lg-end ms-auto mb-1">
                              <div class="dropdown">
                                <div class="btn-group">
                                  <?= getFilterActions($page_config['tables_config']['direcciones']['identifier']); ?>

                                  <?php include 'src/components/per-page.php'; ?>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>

                        <div id="<?= $page_config['tables_config']['direcciones']['identifier']; ?>-table" class="card-body fh-table p-0 sticky-pagination"></div>

                        <!-- CARD LOADING -->
                        <?php include 'src/components/card-loading.php'; ?>
                      </form>
                    </div>

                    <?php if ($customer->type === 'moral') : ?>
                      <div id="tab-sucursales" class="tab-pane <?= $url_ref === 'sucursales' ? 'show active' : ''; ?>">
                        <form id="<?= $page_config['tables_config']['sucursales']['identifier']; ?>-filters-form" autocomplete="off">
                          <div class="card-header bg-white position-sticky top-0" style="z-index: 1;">
                            <div class="row">
                              <div class="col-12 col-lg-8">
                                <div class="row">
                                  <div class="col-12 col-md-6 col-lg-5 mb-2 mb-lg-0">
                                    <div class="form-group">
                                      <label class="form-label" for="sucursales-filter-search">Buscar aquí</label>
                                      <input id="sucursales-filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                                    </div>
                                  </div>

                                  <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">
                                </div>
                              </div>

                              <div class="col-12 col-lg-4 text-center text-lg-end ms-auto mb-1">
                                <div class="dropdown">
                                  <div class="btn-group">
                                    <?= getFilterActions($page_config['tables_config']['sucursales']['identifier']); ?>

                                    <?php include 'src/components/per-page.php'; ?>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <div id="<?= $page_config['tables_config']['sucursales']['identifier']; ?>-table" class="card-body fh-table p-0 sticky-pagination"></div>

                          <!-- CARD LOADING -->
                          <?php include 'src/components/card-loading.php'; ?>
                        </form>
                      </div>
                    <?php endif; ?>

                    <div id="tab-cotizaciones-abiertas" class="tab-pane <?= $url_ref === 'cotizaciones-abiertas' ? 'show active' : ''; ?>">
                      <form id="<?= $page_config['tables_config']['cotizaciones-abiertas']['identifier']; ?>-filters-form" autocomplete="off">
                        <div class="card-header bg-white position-sticky top-0" style="z-index: 1;">
                          <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-abiertas-filter-search">Buscar aquí</label>
                                <input id="cotizaciones-abiertas-filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-abiertas-filter-fecha">Fecha</label>
                                <input id="cotizaciones-abiertas-filter-fecha" class="form-control datepicker" name="fecha" value="" type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-abiertas-filter-id_sucursal_origen">Sucursal</label>
                                <select id="cotizaciones-abiertas-filter-id_sucursal_origen" class="form-control form-select" name="id_sucursal">
                                  <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                                </select>
                              </div>
                            </div>

                            <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">
                          </div>
                        </div>

                        <div id="<?= $page_config['tables_config']['cotizaciones-abiertas']['identifier']; ?>-table" class="card-body fh-table p-0 sticky-pagination"></div>

                        <!-- CARD LOADING -->
                        <?php include 'src/components/card-loading.php'; ?>
                      </form>
                    </div>

                    <div id="tab-cotizaciones-cerradas" class="tab-pane <?= $url_ref === 'cotizaciones-cerradas' ? 'show active' : ''; ?>">
                      <form id="<?= $page_config['tables_config']['cotizaciones-cerradas']['identifier']; ?>-filters-form" autocomplete="off">
                        <div class="card-header bg-white position-sticky top-0" style="z-index: 1;">
                          <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-cerradas-filter-search">Buscar aquí</label>
                                <input id="cotizaciones-cerradas-filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-cerradas-filter-fecha">Fecha</label>
                                <input id="cotizaciones-cerradas-filter-fecha" class="form-control datepicker" name="fecha" value="" type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="cotizaciones-cerradas-filter-id_sucursal_origen">Sucursal</label>
                                <select id="cotizaciones-cerradas-filter-id_sucursal_origen" class="form-control form-select" name="id_sucursal">
                                  <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                                </select>
                              </div>
                            </div>

                            <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">
                          </div>
                        </div>

                        <div id="<?= $page_config['tables_config']['cotizaciones-cerradas']['identifier']; ?>-table" class="card-body fh-table p-0 sticky-pagination"></div>

                        <!-- CARD LOADING -->
                        <?php include 'src/components/card-loading.php'; ?>
                      </form>
                    </div>

                    <div id="tab-ventas" class="tab-pane <?= $url_ref === 'ventas' ? 'show active' : ''; ?>">
                      <form id="<?= $page_config['tables_config']['ventas']['identifier']; ?>-filters-form" autocomplete="off">
                        <div class="card-header bg-white position-sticky top-0" style="z-index: 1;">
                          <div class="row">
                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="ventas-filter-search">Buscar aquí</label>
                                <input id="ventas-filter-search" class="form-control" name="search" placeholder="Folio..." type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="ventas-filter-fecha">Fecha</label>
                                <input id="ventas-filter-fecha" class="form-control datepicker" name="fecha" value="" type="text">
                              </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-2 mb-lg-0">
                              <div class="form-group">
                                <label class="form-label" for="ventas-filter-id_sucursal_origen">Sucursal</label>
                                <select id="ventas-filter-id_sucursal_origen" class="form-control form-select" name="id_sucursal">
                                  <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                                </select>
                              </div>
                            </div>

                            <input name="id_cliente" value="<?= $customer->id; ?>" type="hidden">
                          </div>
                        </div>

                        <div id="<?= $page_config['tables_config']['ventas']['identifier']; ?>-table" class="card-body fh-table p-0 sticky-pagination"></div>

                        <!-- CARD LOADING -->
                        <?php include 'src/components/card-loading.php'; ?>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <?php include 'src/components/footer.php'; ?>

      <!-- MODALS -->
      <!-- COTIZACIONES ABIERTAS -->
      <?php
      $modal_cotizaciones_id = $page_config['tables_config']['cotizaciones-abiertas']['identifier'];
      include 'src/modals/cotizaciones.php';
      ?>

      <!-- COTIZACIONES CERRADAS -->
      <?php
      $modal_cotizaciones_id = $page_config['tables_config']['cotizaciones-cerradas']['identifier'];
      include 'src/modals/cotizaciones.php';
      ?>

      <!-- VENTAS -->
      <?php
      $modal_cotizaciones_id = $page_config['tables_config']['ventas']['identifier'];
      include 'src/modals/cotizaciones.php';
      ?>

      <!-- DIRECCIONES -->
      <?php
      $modal_cliente_direcciones_id = $page_config['tables_config']['direcciones']['identifier'];
      $modal_cliente_direcciones_place = $page_config['page_identifier'];
      include 'src/modals/' . $page_config['tables_config']['direcciones']['identifier'] . '.php';
      ?>

      <!-- SUCURSALES -->
      <?php
      if ($customer->type === 'moral') :
        $modal_cliente_sucursales_id = $page_config['tables_config']['sucursales']['identifier'];
        $modal_cliente_sucursales_place = $page_config['page_identifier'];
        include 'src/modals/' . $page_config['tables_config']['sucursales']['identifier'] . '.php';
      endif;
      ?>
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

  <script src="<?= BASE_URL; ?>/src/main/address-autocomplete.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    // JQUERY ACTIONS
    $(document).on('click', '.btn-ver-productos', function() {
      const data = JSON.parse($(this).attr('data-row'));
      const productos = data.productos;

      const target = $(this).attr('data-bs-target');

      if (target === '#cotizaciones-abiertas-modal-ver-productos') $(`#${PAGE_CONFIG.tables_config['cotizaciones-abiertas'].identifier}-tabla-ver-productos`).html(productos);
      if (target === '#cotizaciones-cerradas-modal-ver-productos') $(`#${PAGE_CONFIG.tables_config['cotizaciones-cerradas'].identifier}-tabla-ver-productos`).html(productos);
      if (target === '#ventas-modal-ver-productos') $(`#${PAGE_CONFIG.tables_config['ventas'].identifier}-tabla-ver-productos`).html(productos);
    });

    function updateURL() {
      const target = $(this).attr('href').replace('#tab-', '');
      const url = `${BASE_URL}/cliente/<?= $customer_id; ?>/${target}`;

      window.history.replaceState({
        ref: target
      }, target, url);
    }

    const cancelProduct = async (saleId, productId) => {
      const alertResponse = await showSweetConfirm({
        title: '¡Cuidado!',
        message: '¿Realmente deseas devolver este producto?'
      });

      if (!alertResponse) return;

      callEndpoint({
        place: PAGE_CONFIG.page_identifier,
        parameters: {
          saleId,
          productId,
          action: 'cancel-product'
        }
      }).then(response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        if (response.status === 'success') $(`#${PAGE_CONFIG.tables_config['ventas'].identifier}-tabla-ver-productos`).html(response.products);
        if (response.reload) load(1, PAGE_CONFIG.page_identifier);
      });
    };

    $('.nav-pills .nav-link').on('click', updateURL);
    $('#cotizaciones-abiertas-filter-fecha').on('change', () => load(1, PAGE_CONFIG.tables_config['cotizaciones-abiertas'].identifier));
    $('#cotizaciones-cerradas-filter-fecha').on('change', () => load(1, PAGE_CONFIG.tables_config['cotizaciones-cerradas'].identifier));
    $('#ventas-filter-fecha').on('change', () => load(1, PAGE_CONFIG.tables_config['ventas'].identifier));

    $("#cliente-panel-header a").on("click", function() {
      const place = $(this).attr('data-place');
      load(1, place);
    });
  </script>
</body>

</html>