<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Calculadora de llantas y rines',
  'page_identifier'   => 'calculadora-de-llantas-y-rines'
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
                <div id="ws-widget-fbd29f"></div>

                <script src="//services.wheel-size.com/code/ws-widget.js"></script>

                <script>
                  var widget = WheelSizeWidgets.create('#ws-widget-fbd29f', {
                    uuid: 'fbd29fb0148541eabbe07e2c87df7ffa',
                    type: 'finder-v2',
                    width: '100%'
                  });
                </script>
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