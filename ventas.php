<?php
include 'inc/session.inc.php';

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$page_config = [
  'page_title'        => 'Ventas',
  'page_identifier'   => 'ventas',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$folio = cleanStr($_GET["folio"] ?? "");

// Obtener los vendedores de la venta por sucursal si no es admin
$branchId = !$IS_ADMIN ? getSessionBranchOfficeId() : null;
$byBranchId = $branchId ? "V.id_sucursal = {$branchId}" : "1=1";

$query = "SELECT
    V.id_usuario AS value,
    U.nombre_completo AS label
  FROM
    {$db_dti}_ventas AS V
  INNER JOIN {$db_ati}_usuarios AS U ON
    V.id_usuario = U.id_usuario
  WHERE
    ({$byBranchId})
  GROUP BY
    V.id_usuario
  ORDER BY
    U.nombre_completo
";

$result   = mysqli_query($mysqli, $query);
$numRows  = mysqli_num_rows($result);

$suppliers = [];

if ($numRows > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $suppliers[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERY UI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">

  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            "pageDescription" => "Monitorea tus ventas en tiempo real, gestiona el timbrado fiscal y controla tus ingresos por sucursal",
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "extraHtmlInFilters" => <<<HTML
              <input id="selected-sales" name="selectedSales" type="hidden">
            HTML,
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Folios...",
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
                "name"          => "id_cliente",
                "label"         => "Cliente",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                ],
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
              ],
              [
                "field"         => "select",
                "name"          => "tipo_productos",
                "label"         => "Tipo de productos",
                "optionsRender" => renderToString(getProductTypesCatalog("", "--Todos--"))
              ],
              [
                "field"         => "select",
                "name"          => "forma_pago",
                "label"         => "Forma de pago",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ["value" => "credito", "label" => "Crédito"],
                  ["value" => "contado", "label" => "Contado"]
                ],
              ],
              [
                "field"         => "select",
                "name"          => "metodo_pago",
                "label"         => "Método de pago",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ["value" => "efectivo", "label" => "Efectivo"],
                  ["value" => "cheque", "label" => "Cheque"],
                  ["value" => "transferencia", "label" => "Transferencia"],
                  ["value" => "tarjeta_credito", "label" => "Tarjeta de crédito"],
                  ["value" => "tarjeta_debito", "label" => "Tarjeta de débito"]
                ],
              ],
              [
                "field"         => "select",
                "name"          => "id_quien_realizo",
                "label"         => "Quien realizó",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ...$suppliers
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

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <!-- SELECT2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <!-- SELECT2 AUTOCOMPLETE -->
  <script src="<?= BASE_URL; ?>/src/js/select2.autocomplete.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $(function() {
      const customersAutocomplete = new Select2Autocomplete({
        selector: "#filter-ventas-id_cliente",
        url: "<?= BASE_URL; ?>/data/autocompletes/clientes_data.php",
        onSelect: data => {
          $('#ventas-filters-form').submit();
        }
      });
    });

    $(document).on('click', '.btn-ver-productos', function() {
      const data = JSON.parse($(this).attr('data-row'));
      const productos = data.productos;

      $('#tabla-ver-productos').html(productos);
    });

    $('#filter-fecha').on('change', () => load(1, PAGE_CONFIG.page_identifier));

    const cancelProduct = async (saleId, productId) => {
      const alertResponse = await showSweetConfirm({
        title: '¡Cuidado!',
        message: '¿Realmente deseas devolver este producto?'
      });

      if (!alertResponse) return;

      callEndpoint({
        place: PAGE_CONFIG.page_identifier,
        parameters: {
          saleId,
          productId,
          action: 'cancel-product'
        }
      }).then(response => {
        if (response.toastMessage) showSweetToast({
          icon: response.status,
          message: response.toastMessage
        });

        if (response.status === 'success') $('#tabla-ver-productos').html(response.products);
        if (response.reload) load(1, PAGE_CONFIG.page_identifier);
      });
    };
  </script>
</body>

</html>