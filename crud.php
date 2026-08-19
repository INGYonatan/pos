<?php
include 'inc/session.inc.php';

$module_slug = cleanStr($_GET['module_slug']);
$data_modulo = getModuleDataBySlug($module_slug);

$page_config = [
  'page_title'        => $data_modulo['modulo'],
  'page_identifier'   => $data_modulo['slug'],
  'modal_title_add'   => 'Agregar',
  'modal_title_edit'  => 'Editar'
];

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
            <div class="col-md-12 col-lg-12">
              <form id="<?= $page_config['page_identifier']; ?>-filters-form" class="card" autocomplete="off">
                <div class="card-header bg-white">
                  <div class="row">
                    <div class="col-12 col-lg-9">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-2 mb-lg-0">
                          <input id="filter-search" class="form-control" name="search" placeholder="Buscar aqui..." type="search">
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3 text-center text-lg-end">
                      <div class="dropdown">
                        <div class="btn-group">
                          <?= getFilterActions($page_config['page_identifier']); ?>

                          <?php include 'src/components/per-page.php'; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="<?= $page_config['page_identifier']; ?>-table" class="card-body fh-table p-0"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $page_config['page_identifier'] . '.php'; ?>

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

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/crud-init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>