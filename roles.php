<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Roles',
  'page_identifier'   => 'roles',
  'modal_title_add'   => 'Agregar rol',
  'modal_title_edit'  => 'Editar rol'
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
          <?php renderComponent("crudtable", [
            "pageId"          => $page_config['page_identifier'],
            "pageTitle"       => $page_config['page_title'],
            "pageDescription" => "Administra los roles del sistema, controla los accesos de cada perfil y gestiona la seguridad de la plataforma",
            "renderedActions" => checkModuleActionPermission($page_config['page_identifier'], 'agregar')
              ? '<button class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#' . $page_config['page_identifier'] . '-modal" type="button"><i class="fe-plus"></i> Nuevo</button>'
              : '',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Rol...",
              ]
            ]
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

  <script src="<?= BASE_URL; ?>/src/plugins/datatable/datatable.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/datatable/datatable-init.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
