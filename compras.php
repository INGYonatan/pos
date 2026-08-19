<?php
include 'inc/session.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Compras',
  'page_identifier'   => 'compras',
  'modal_title_add'   => '',
  'modal_title_edit'  => 'Editar compra'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

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
            "pageDescription" => "Administra tus compras registradas, consulta el desglose de impuestos y gestiona tus comprobantes",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "filters" => [
              "principal" => [
                [
                  "name"        => "search",
                  "label"       => "Buscar aquí",
                  "type"        => "input",
                  "placeholder" => "Folio, No. Documento, " . ($IS_ADMIN ? "Sucursal, " : "") . "Realizó, Total...",
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

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $(document).on('click', '.btn-ver-productos', function() {
      const data = JSON.parse($(this).attr('data-row'));
      const productos = data.productos;

      $('#tabla-ver-productos').html(productos);
    });

    $('#filter-fecha').on('change', () => load(1, PAGE_CONFIG.page_identifier));
  </script>
</body>

</html>