<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Líneas',
  'page_identifier'   => 'categorias',
  'modal_title_add'   => 'Agregar línea',
  'modal_title_edit'  => 'Editar línea'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$brandId = cleanStr($_GET["uid"]);

if ($brandId) {
  $query    = "SELECT * FROM {$db_dti}_marcas WHERE id_marca = $brandId LIMIT 1";
  $result   = mysqli_query($mysqli, $query);
  $numRows  = mysqli_num_rows($result);

  if ($numRows === 0) {
    // script para regresar con history back
    echo "<script>
          window.history.back();
        </script>";
    die;
  }

  $row = mysqli_fetch_assoc($result);
  $brandName = $row['marca'];

  $page_config['page_title'] = "$brandName - Lineas";
}
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
            "pageDescription" => "Administra las líneas de productos de tu catálogo, ordénalas por marca y mantenlas actualizadas",
            "actions"         => renderToString(
              '<a class="btn btn-light btn-sm" href="javascript:window.history.back();">
                <i class="mdi mdi-arrow-left"></i> Volver
              </a>' .
              getFilterActions($page_config['page_identifier'])
            ),
            "extraHtmlInFilters" => '<input name="id_marca" value="' . $brandId . '" type="hidden">',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Línea, Marca...",
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
