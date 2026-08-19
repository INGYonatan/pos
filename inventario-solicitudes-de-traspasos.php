<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Solicitudes de traspasos',
  'page_identifier'   => 'inventario-solicitudes-de-traspasos',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];
$pageId   = $page_config['page_identifier'];
$type     = $_GET["type"] ?? "realizadas";
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
          <ul class="nav nav-pills nav-fill mb-3">
            <li class="nav-item">
              <a class="nav-link <?= $type == "realizadas" ? "active text-dark" : "border"; ?>" aria-current="page" href="<?= BASE_URL; ?>/inventario-solicitudes-de-traspasos/realizadas">
                <i class="fa fa-check me-1"></i>
                Solicitados
              </a>
            </li>

            <li class="nav-item">
              <a class="nav-link <?= $type == "recibidas" ? "active text-dark" : "border"; ?>" href="<?= BASE_URL; ?>/inventario-solicitudes-de-traspasos/recibidas">
                <i class="fa fa-list me-1"></i>
                Recibidas
              </a>
            </li>
          </ul>

          <?php renderComponent("solicitudes-de-traspasos-list", [
            "pageId"    => $pageId,
            "type"      => $type,
            "IS_ADMIN"  => $IS_ADMIN
          ]); ?>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/ver-productos.php'; ?>

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

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script src="<?= BASE_URL; ?>/src/main/inventario-traspasos-recibidos.js"></script>

  <script>
    // $(document).on('click', '.btn-ver-productos', function() {
    //   const data = JSON.parse($(this).attr('data-row'));
    //   const productos = data.productos;

    //   $('#tabla-ver-productos').html(productos);
    // });
  </script>
</body>

</html>