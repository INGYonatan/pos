<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Menú de navegación',
  'page_identifier'   => 'menu',
  'modal_title_add'   => 'Agregar item del menú',
  'modal_title_edit'  => 'Editar item del menú'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">
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
        <div class="container">
          <?php renderComponent("crudtable", [
            "pageId"          => $page_config['page_identifier'],
            "pageTitle"       => $page_config['page_title'],
            "pageDescription" => "Administra los ítems del menú de navegación, ordénalos y organiza la estructura de acceso del sistema",
            "renderedActions" => checkModuleActionPermission($page_config['page_identifier'], 'agregar')
              ? '<button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#' . $page_config['page_identifier'] . '-modal" type="button"><i class="fe-plus"></i> Nuevo</button>'
              : '',
            "filters" => []
          ]); ?>
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

  <!-- JQUERY UI -->
  <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/datatable/datatable.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/datatable/datatable-init.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
