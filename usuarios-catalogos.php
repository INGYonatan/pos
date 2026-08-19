<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Catálogo de usuarios',
  'page_identifier'   => 'usuarios-catalogos',
  'modal_title_add'   => 'Agregar archivo',
  'modal_title_edit'  => 'Editar archivo'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$userId = $_GET["uid"];

$query = "SELECT id_usuario, nombre_completo FROM adm_usuarios WHERE MD5(id_usuario) = ?";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $userId);
$stmt->execute();

$result = $stmt->get_result();
$numRows = $result->num_rows;

if ($numRows == 0) {
  closeSession();
  die;
}

$data = $result->fetch_assoc();

$page_config["page_title"] = "Catálogo de archivos de {$data['nombre_completo']}";

$userId = $data['id_usuario'];
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
            "pageDescription" => "Consulta los archivos del catálogo del usuario, agrega documentos y mantén actualizados sus recursos",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "extraHtmlInFilters" => '<input name="userId" value="' . $userId . '" type="hidden">',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Archivo...",
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

  <!-- MULTIDATATABLE JS -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <!-- VALIDATE JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>
