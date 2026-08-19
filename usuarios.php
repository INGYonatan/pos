<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Usuarios',
  'page_identifier'   => 'usuarios',
  'modal_title_add'   => 'Agregar usuario',
  'modal_title_edit'  => 'Editar usuario'
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
            "pageDescription" => "Administra los usuarios del sistema, asigna roles y sucursales, y controla el acceso de cada colaborador",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Nombre, Correo, Teléfono...",
              ],
              [
                "field"         => "select",
                "name"          => "id_rol",
                "label"         => "Rol",
                "optionsRender" => renderToString(getRolesCatalog("", "--Todos los roles--")),
              ],
              [
                "field"         => "select",
                "name"          => "id_sucursal",
                "label"         => "Sucursal",
                "optionsRender" => renderToString(getBranchOfficesCatalog("", "--Todas las sucursales--")),
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

  <script src="<?= BASE_URL; ?>/src/main/<?= $page_config['page_identifier']; ?>.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
