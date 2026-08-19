<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Panel de control',
  'page_identifier'   => 'panel-de-control'
];
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
            <div class="card">
              <div class="card-body">
                <h3 class="card-title">Bienvenido al sistema de <?= ADM_NAME; ?></h3>
              </div>
            </div>
          </div>
        </div>
        
      </div>

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
</body>

</html>