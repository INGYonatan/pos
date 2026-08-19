<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Tipo de cambio',
  'page_identifier'   => 'tipo-cambio',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$query = "SELECT
    id_configuracion,
    configuracion,
    slug,
    valor,
    tipo
  FROM
    {$db_dti}_configuraciones
  WHERE
    slug = 'tipo_cambio'
  LIMIT 1
";

$query_result = mysqli_query($mysqli, $query);
$setting      = mysqli_fetch_assoc($query_result);
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
          <form id="<?= $page_config['page_identifier']; ?>-form-data" class="row form-validate" autocomplete="off">
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
              <div class="card">
                <div class="card-header bg-primary">
                  <h3 class="header-title text-dark m-0">Tipo de cambio</h3>
                </div>

                <div class="card-body">
                  <div class="row">
                    <div class="col-12">
                      <div class="form-group">
                        <label class="form-label" for="tipo_cambio">Tipo de cambio</label>
                        <input id="tipo_cambio" class="form-control number-input" name="tipo_cambio" value="<?= $setting['valor']; ?>" type="text" required>
                      </div>
                    </div>
                  </div>
                </div>

                <input name="uid" value="<?= $setting['id_configuracion']; ?>" type="hidden">
                <input name="action" value="edit-<?= $page_config['page_identifier']; ?>" type="hidden">
                <input name="place" value="<?= $page_config['page_identifier']; ?>" type="hidden">

                <div class="card-footer text-end">
                  <button class="btn btn-primary" type="submit">
                    <i class="fa fa-check-circle"></i>
                    Guardar cambios
                  </button>
                </div>
              </div>
            </div>
          </form>
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

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>