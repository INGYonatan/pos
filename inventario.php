<?php
include 'inc/session.inc.php';

$page_config = [
  'page_title'        => 'Valor de inventario',
  'page_identifier'   => 'inventario',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];
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
            "pageDescription" => "Monitorea los niveles de stock, filtra tus existencias y analiza el historial de tus productos",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "renderedActions" => <<<HTML
              <button id="btn-print-inventory" class="btn btn-primary" type="button">
                <i class="fa fa-print"></i><br>Imprimir
              </button>
            HTML,
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "placeholder" => "Código, Producto...",
              ],
              [
                "field"         => "select",
                "name"          => "id_sucursal",
                "label"         => "Sucursal",
                "optionsRender" => renderToString(getBranchOfficesCatalog("", "--Todas--", true))
              ],
              [
                "field"         => "select",
                "name"          => "brandId",
                "label"         => "Marca",
                "optionsRender" => renderToString(getBrandsCatalog("", "--Todas--")),
                "attributes"    => [
                  "catalog-onChange" => "#filter-inventario-categoryId",
                  "data-parameters"  => htmlentities(json_encode(['action' => 'get-brand-categories'])),
                  "data-resetCatalog" => "true"
                ],
              ],
              [
                "field"         => "select",
                "name"          => "categoryId",
                "label"         => "Línea",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todas--"],
                ],
                "attributes"    => [
                  "catalog-onChange" => "#filter-inventario-familyId",
                  "data-parameters"  => htmlentities(json_encode(['action' => 'get-category-families'])),
                  "data-resetCatalog" => "true"
                ],
              ],
              [
                "field"         => "select",
                "name"          => "familyId",
                "label"         => "Familia",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todas--"],
                ],
              ],
              [
                "field"         => "select",
                "name"          => "existenceMode",
                "label"         => "Existencia",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ["value" => "con-existencia", "label" => "Con existencia", "selected" => true],
                  ["value" => "sin-existencia", "label" => "Sin existencia"],
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

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    const printInventory = () => {
      const idSucursal = $('#filter-<?= $page_config["page_identifier"]; ?>-id_sucursal').val();
      const brandId = $("#filter-<?= $page_config["page_identifier"]; ?>-brandId").val();
      const categoryId = $("#filter-<?= $page_config["page_identifier"]; ?>-categoryId").val();
      const familyId = $("#filter-<?= $page_config["page_identifier"]; ?>-familyId").val();
      const existenceMode = $("#filter-<?= $page_config["page_identifier"]; ?>-existenceMode").val();

      const params = new URLSearchParams();

      if (idSucursal) params.append('sid', idSucursal);
      if (brandId) params.append('brandId', brandId);
      if (categoryId) params.append('categoryId', categoryId);
      if (familyId) params.append('familyId', familyId);
      if (existenceMode == "con-existencia") params.append('hideStockZero', 'si');
      if (existenceMode == "sin-existencia") params.append('hideStockZero', 'no');

      let url = `${BASE_URL}/pdf-inventario.php`;

      if ([...params].length) url += `?${params.toString()}`;

      window.open(url, '_blank');
    }

    $('#btn-print-inventory').on('click', () => printInventory());
  </script>
</body>

</html>