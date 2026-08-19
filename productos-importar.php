<?php
include 'inc/session.inc.php';

// require_once __DIR__ . '/../models/categorias.model.php';
// require_once __DIR__ . "/../models/productos.model.php";

$page_config = [
  'page_title'        => 'Importar productos',
  'page_identifier'   => 'productos',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
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
          <!-- PAGE LOADINGS -->
          <?php include 'src/components/page-loadings.php'; ?>

          <!-- REQUIRED SCRIPTS -->
          <?php include 'src/components/required-scripts.php'; ?>

          <!-- APP JS -->
          <script src="<?= BASE_URL; ?>/src/js/app.min.js"></script>

          <div class="row">
            <div class="col-12">
              <?php renderComponent("form-importar-productos"); ?>
            </div>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <?php include 'src/components/footer.php'; ?>
    </div>
  </div>
  <!-- END wrapper -->
</body>

</html>