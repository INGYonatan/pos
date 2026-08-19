<?php
include 'inc/session.inc.php';
include 'data/lib/helpers/catalogs.helper.php';
require_once __DIR__ . "/data/lib/helpers/emisores.helpers.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Facturas de nota de crédito',
  'page_identifier'   => 'facturas-nota-credito'
  //'modal_title_add'   => 'Agregar cliente',
  //'modal_title_edit'  => 'Editar cliente'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$emisoresHelper   = new EmisoresHelper();
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">
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
            "pageDescription" => "Gestiona tus comprobantes fiscales digitales, descarga archivos XML/PDF y controla el estatus de tus envíos",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "placeholder" => "Folio, emisor, Cliente," . ($IS_ADMIN ? " Sucursal," : "") . " F. Pago, Monto...",
              ],
              [
                "field"  => "render",
                "render" => getComponent("field-fechas-desde-hasta")
              ],
              [
                "field"         => "select",
                "name"          => "id_sucursal",
                "label"         => "Sucursal",
                "optionsRender" => renderToString(getBranchOfficesCatalog("", "--Todas--", true)),
                "visible"       => $IS_ADMIN ? true : false,
              ],
              [
                "field"         => "select",
                "name"          => "status",
                "label"         => "Estatus",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ["value" => "activo", "label" => "Activo", "selected" => true],
                  ["value" => "cancelado", "label" => "Cancelado"]
                ],
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

  <!-- SELECT2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/select2.autocomplete.js"></script>

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $("#fd-motivo").on("change", function() {
      if ($(this).val() == "01") {
        $("#fd-folioSustitutoContainer").show();
        $("#fd-folioSustituto").prop("required", true);
      } else {
        $("#fd-folioSustitutoContainer").hide();
        $("#fd-folioSustituto").prop("required", false);
      }
    });

    const autocompleteCustomers = new Select2Autocomplete({
      selector: "#filter-id_cliente",
      url: "<?= BASE_URL; ?>/data/autocompletes/clientes_data.php",
      onselect: () => $("#filter-id_cliente").trigger("change")
    });
  </script>
</body>

</html>