<?php
include 'inc/session.inc.php';
require 'data/lib/helpers/categories.helper.php';

$category_id  = cleanStr($_GET['uid']);
$category     = categories_get_by_id($category_id);

$page_config = [
  'page_title'        => "Familias",
  'page_identifier'   => 'categoria-familias',
  'modal_title_add'   => 'Agregar familia',
  'modal_title_edit'  => 'Editar familia'
];

if ($category) {
  $page_config['page_title'] = "{$category->name} | Familias";
}

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
            "pageDescription" => "Consulta y administra las familias de cada línea, ajusta límites de descuento y precios de mayoreo",
            "actions"         => renderToString(
              '<a class="btn btn-light btn-sm" href="javascript:window.history.back();">
                <i class="mdi mdi-arrow-left"></i> Volver
              </a>' .
              getFilterActions($page_config['page_identifier'])
            ),
            "extraHtmlInFilters" => $category_id ? '<input name="id_categoria" value="' . $category_id . '" type="hidden">' : '',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Familia...",
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

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
