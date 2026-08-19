<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'      => 'Usuarios permisos',
  'page_identifier' => 'usuarios-permisos'
];

$pageId = $page_config["page_identifier"];

checkModuleActionPermission($pageId, 'ver', true);

$uid = cleanStr($_GET["uid"]);

if (!$uid) {
  closeSession();
  die;
}

// validar si el usuario existe
$query    = "SELECT * FROM adm_usuarios WHERE MD5(id_usuario) = '{$uid}' LIMIT 1";
$result   = mysqli_query($mysqli, $query);
$numRows  = mysqli_num_rows($result);

if ($numRows == 0) {
  closeSession();
  die;
}

$userData = mysqli_fetch_assoc($result);

$page_config["page_title"] = "Permisos :: {$userData['nombre_completo']}";
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
      <div class="pt-0 content">
        <div class="container-fluid">
          <?php renderComponent("crudtable", [
            "pageId"          => $page_config['page_identifier'],
            "pageTitle"       => $page_config['page_title'],
            "pageDescription" => "Administra los permisos y accesos del usuario por módulo, activa o desactiva las acciones permitidas",
            "extraHtmlInFilters" => '<input name="uid" value="' . $uid . '" type="hidden">',
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Módulo...",
              ]
            ]
          ]); ?>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $pageId . '.php'; ?>

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

  <script>
    $(document).on("click", ".toggle-all-permissions", function() {
      const dataSwitches = $(this).attr("data-switches");
      const isChecked = $(this).is(":checked");

      if (!isChecked) $(`.${dataSwitches}`).prop("checked", true).trigger("click");
      if (isChecked) $(`.${dataSwitches}`).prop("checked", false).trigger("click");
    });

    const addPermission = (moduleId, moduleActionId, parentAll) => callEndpoint({
      place: "usuarios-permisos",
      parameters: {
        action: "add-permission-<?= $pageId; ?>",
        uid: "<?= $uid; ?>",
        moduleId,
        moduleActionId
      }
    }).then(res => {
      if (res.toastMessage) showSweetToast({
        icon: res.status,
        message: res.toastMessage
      });

      let isCheckedAll = true;

      $(`[data-parentAll='${parentAll}']`).each(function() {
        if (!$(this).is(":checked")) isCheckedAll = false;
      });

      if (isCheckedAll) $(`#${parentAll}`).prop("checked", true);
      if (!isCheckedAll) $(`#${parentAll}`).prop("checked", false);
    });

    const removePermission = (moduleId, moduleActionId, parentAll) => callEndpoint({
      place: "usuarios-permisos",
      parameters: {
        action: "remove-permission-<?= $pageId; ?>",
        uid: "<?= $uid; ?>",
        moduleId,
        moduleActionId
      }
    }).then(res => {
      if (res.toastMessage) showSweetToast({
        icon: res.status,
        message: res.toastMessage
      });

      $(`#${parentAll}`).prop("checked", false);
    });
  </script>
</body>

</html>
