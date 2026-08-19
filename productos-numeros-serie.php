<?php
include 'inc/session.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Números de serie',
  'page_identifier'   => 'productos-numeros-serie',
  'modal_title_add'   => '',
  'modal_title_edit'  => 'Editar número de serie',
  "tables_config" => [
    "productos-numeros-serie-disponibles" => [
      "identifier" => "productos-numeros-serie-disponibles",
    ],
    "productos-numeros-serie-vendidos" => [
      "identifier" => "productos-numeros-serie-vendidos",
    ]
  ]
];

$availableSNTableId = $page_config['tables_config']['productos-numeros-serie-disponibles']['identifier'];
$soldSNTableId      = $page_config['tables_config']['productos-numeros-serie-vendidos']['identifier'];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>
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
            <div class="col-md-12 col-lg-6">
              <form id="<?= $availableSNTableId; ?>-filters-form" class="card" autocomplete="off">
                <div class="card-header bg-success">
                  <div class="row">
                    <div class="col-12 mb-3">
                      <h4 class="card-title text-white m-0">Números de serie disponibles</h4>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-lg-9">
                      <div class="row">
                        <div class="col-12 col-md-6 mb-2 mb-lg-0">
                          <div class="form-group">
                            <label class="form-label text-white" for="filter-search">Buscar</label>
                            <input id="filter-search" class="form-control" name="search" placeholder="Número de serie, Código producto" type="text">
                          </div>
                        </div>

                        <?php if ($IS_ADMIN) : ?>
                          <div class="col-12 col-md-6 mb-2 mb-lg-0">
                            <div class="form-group">
                              <label class="form-label text-white" for="filter-id_sucursal">Sucursal</label>
                              <select id="filter-id_sucursal" class="form-control form-select" name="id_sucursal">
                                <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                              </select>
                            </div>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3 text-center text-lg-end">
                      <div class="dropdown">
                        <div class="btn-group">
                          <?php include 'src/components/per-page.php'; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="<?= $availableSNTableId; ?>-table" class="card-body fh-table p-0"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>
              </form>
            </div>

            <div class="col-md-12 col-lg-6">
              <form id="<?= $soldSNTableId; ?>-filters-form" class="card" autocomplete="off">
                <div class="card-header bg-warning">
                  <div class="row">
                    <div class="col-12 mb-3">
                      <h4 class="card-title text-white m-0">Números de serie vendidos</h4>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-12 col-lg-9">
                      <div class="row">
                        <div class="col-12 col-md-6 mb-2 mb-lg-0">
                          <div class="form-group">
                            <label class="form-label text-white" for="filter-search-2">Buscar</label>
                            <input id="filter-search-2" class="form-control" name="search" placeholder="Número de serie, Código producto" type="text">
                          </div>
                        </div>

                        <?php if ($IS_ADMIN) : ?>
                          <div class="col-12 col-md-6 mb-2 mb-lg-0">
                            <div class="form-group">
                              <label class="form-label text-white" for="filter-id_sucursal-2">Sucursal</label>
                              <select id="filter-id_sucursal-2" class="form-control form-select" name="id_sucursal">
                                <?= getBranchOfficesCatalog('', '--Todas--', true); ?>
                              </select>
                            </div>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3 text-center text-lg-end">
                      <div class="dropdown">
                        <div class="btn-group">
                          <?php include 'src/components/per-page.php'; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="<?= $soldSNTableId; ?>-table" class="card-body fh-table p-0"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $page_config["page_identifier"] . '.php'; ?>

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

  <!-- MULTIDATATABLE JS -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <!-- VALIDATE JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $(document).on("click", ".btn-edit", function() {
      const data = JSON.parse($(this).attr("data-row"));

      $("#fdt-serialNumber").val(data.serialNumber);
      $("#fdt-uid").val(data.uid);
    });
  </script>
</body>

</html>