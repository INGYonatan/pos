<?php
include 'inc/session.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Ventas Facturadas',
  'page_identifier'   => 'reporte-ventas-facturadas',
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
          <div class="row">
            <div class="col-12 col-lg-9">
              <?php renderComponent("crudtable", [
                "pageId"          => $page_config['page_identifier'],
                "pageTitle"       => $page_config['page_title'],
                "pageDescription" => "Reporte de ventas facturadas por día, consulta los totales por depositar y descarga el acumulado mensual",
                "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
                "extraHtmlInFilters" => '<input id="filter-date" name="date" value="' . date("Y-m-d") . '" type="hidden">',
                "filters" => [
                  [
                    "name"        => "search",
                    "label"       => "Buscar aquí",
                    "type"        => "input",
                    "placeholder" => "Folio, Cliente" . ($IS_ADMIN ? ", Sucursal" : "") . "...",
                  ],
                  [
                    "field"         => "select",
                    "name"          => "id_sucursal",
                    "label"         => "Sucursal",
                    "optionsRender" => renderToString(getBranchOfficesCatalog("", "--Todas--", true)),
                    "visible"       => $IS_ADMIN ? true : false,
                  ]
                ]
              ]); ?>
            </div>

            <div id="stats-container" class="col-12 col-lg-3"></div>
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

  <!-- MULTIDATATABLE JS -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <!-- VALIDATE JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
