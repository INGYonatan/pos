<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Multitabla',
  'page_identifier'   => 'multitabla',
  //'modal_title_add'   => 'Agregar usuario',
  //'modal_title_edit'  => 'Editar usuario'
  'tables_config'     => [
    'tabla_1' => [
      'identifier' => 'tabla_1'
    ],
    'tabla_2' => [
      'identifier' => 'tabla_2'
    ]
  ]
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
            <div class="col-12">
              <div class="page-title-box">
                

                <div class="page-title-right">
                  <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Configuración</a></li>
                    <li class="breadcrumb-item active"><?= $page_config['page_title']; ?></li>
                  </ol>
                </div>
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 col-lg-12">
              <form id="<?= $page_config['tables_config']['tabla_1']['identifier']; ?>-filters-form" class="card card-overlay" autocomplete="off">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-lg-9">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-2 mb-lg-0">
                          <input id="<?= $page_config['tables_config']['tabla_1']['identifier']; ?>-search" class="form-control" name="search" placeholder="Buscar aqui..." type="text">
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3 text-center text-lg-end">
                      <div class="dropdown">
                        <div class="btn-group">
                          <?php /* if (checkModuleActionPermission($page_config['page_identifier'], 'agregar')) : ?>
                            <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#<?= $page_config['page_identifier']; ?>-modal" type="button">
                              <i class="fe-plus"></i> Nuevo
                            </button>
                          <?php endif; */ ?>

                          <?php include 'src/components/per-page.php'; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="<?= $page_config['tables_config']['tabla_1']['identifier']; ?>-table" class="card-body"></div>

                <!-- CARD LOADING -->
                <?php include 'src/components/card-loading.php'; ?>
              </form>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 col-lg-12">
              <form id="<?= $page_config['tables_config']['tabla_2']['identifier']; ?>-filters-form" class="card card-overlay" autocomplete="off">
                <div class="card-body">
                  <div class="row">
                    <div class="col-12 col-lg-9">
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-2 mb-lg-0">
                          <input id="<?= $page_config['tables_config']['tabla_2']['identifier']; ?>-search" class="form-control" name="search" placeholder="Buscar aqui..." type="text">
                        </div>
                      </div>
                    </div>

                    <div class="col-12 col-lg-3 text-center text-lg-end">
                      <div class="dropdown">
                        <div class="btn-group">
                          <?php /* if (checkModuleActionPermission($page_config['page_identifier'], 'agregar')) : ?>
                            <button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#<?= $page_config['page_identifier']; ?>-modal" type="button">
                              <i class="fe-plus"></i> Nuevo
                            </button>
                          <?php endif; */ ?>

                          <?php include 'src/components/per-page.php'; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div id="<?= $page_config['tables_config']['tabla_2']['identifier']; ?>-table" class="card-body"></div>

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
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>
  <!-- <script src="<?= BASE_URL; ?>/src/main/<?= $page_config['page_identifier']; ?>.js"></script> -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>