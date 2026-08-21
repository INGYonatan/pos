<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/customers.helper.php";

$page_config = [
  'page_title'        => 'Estado de cuenta',
  'page_identifier'   => 'cuentas-por-cobrar-estado-de-cuenta',
  'modal_title_add'   => 'Agregar tipo',
  'modal_title_edit'  => 'Editar tipo'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$customerMd5Id = cleanStr($_GET["uid"]);

if (!$customerMd5Id) {
  closeSession();
  exit;
}

$customersModel = new CustomerHelper();
$customersModel->getByMd5Id($customerMd5Id);

if (!$customersModel->getId()) {
  closeSession();
  exit;
}
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
          <?php renderComponent("page-title", [
            "pageTitle" => "Estado de cuenta",
            "pageDescription" => "Consulta el saldo detallado del cliente, registra nuevos pagos y revisa el historial completo de ventas y abonos"
          ]); ?>

          <div class="row">
            <div class="col-12 col-lg-6 mb-3">
              <div class="card h-100">
                <div class="card-body">
                  <h3 class="header-title"><?= $customersModel->getName(); ?></h3><br>
                  RFC: <?= $customersModel->getRfc(); ?><br>
                  Email: <?= $customersModel->getEmail(); ?><br>
                  <span><abbr title="Phone">Tel:</abbr> <?= $customersModel->getPhone() ? formatPhoneNumber($customersModel->getPhone()) : "--"; ?></span>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6 mb-3">
              <div class="card h-100">
                <div class="card-body">
                  <h3 class="header-title">Resumen de la cuenta</h3> <br>

                  <address id="account-summary">
                    <span class="fw-bold">Monto total vendido:</span> <span id="totalAmount"></span><br>
                    <span class="fw-bold">Total abonado:</span> <span id="totalPaid"></span><br>
                    <span class="fw-bold">Saldo pendiente:</span> <span id="balance"></span>
                  </address>
                </div>
              </div>
            </div>
          </div>

          <?php renderComponent("crudtable", [
            "pageId"          => $page_config['page_identifier'],
            "actions"         => renderToString(getFilterActions($page_config['page_identifier'])),
            "extraHtmlInFilters" => <<<HTML
              <input name="customerId" value="{$customerMd5Id}" type="hidden">
            HTML,
            "filters" => [
              [
                "name"        => "search",
                "label"       => "Buscar aquí",
                "type"        => "input",
                "placeholder" => "Folio...",
              ],
              [
                "field"  => "render",
                "render" => getComponent("field-fechas-desde-hasta")
              ],
              [
                "field"         => "select",
                "name"          => "status",
                "label"         => "Estatus",
                "selectOptions" => [
                  ["value" => "", "label" => "--Todos--"],
                  ["value" => "pagado", "label" => "Pagado"],
                  ["value" => "pendiente", "label" => "Pendiente"],
                ],
              ]
            ]
          ]); ?>
        </div>
      </div>

      <!-- MODALS -->
      <!-- MODALS -->
      <?php
      $modal_ventas_pagos_id      = "ventas-pagos";
      $modal_ventas_pagos_origin  = "cuentas-por-cobrar-estado-de-cuenta";

      include 'src/modals/ventas-pagos.php';
      ?>

      <div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal-ver-productos" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
            <div class="modal-header bg-primary">
              <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Productos</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="tabla-ver-productos" class="modal-body"></div>

            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
            </div>
          </form>
        </div>
      </div>

      <div class="modal fade" id="<?= $page_config['page_identifier']; ?>-modal-ver-pagos" tabindex="-1" role="dialog" aria-labelledby="<?= $page_config['page_identifier']; ?>-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
          <form id="<?= $page_config['page_identifier']; ?>-form-data" class="modal-content form-validate" autocomplete="off">
            <div class="modal-header bg-primary">
              <h5 class="modal-title" id="<?= $page_config['page_identifier']; ?>-modal-label">Productos</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div id="tabla-ver-pagos" class="modal-body"></div>

            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cerrar</button>
            </div>
          </form>
        </div>
      </div>

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

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $(document).on("click", ".btn-delete-payment", async function() {
      const uid = $(this).data("uid");

      const alertResponse = await showSweetConfirm({
        title: "¡Cuidado!",
        message: "¿Realmente deseas eliminar este pago?<br><span class='text-danger'>Esta acción no se puede deshacer.</span>",
      });

      if (!alertResponse) return;

      callEndpoint({
        place: "ventas-pagos",
        parameters: {
          action: "action-eliminar-ventas-pagos",
          uid
        }
      }).then(response => {
        if (response.status != "success") {
          showSweetToast({
            icon: response.status,
            message: response.alertMessage || response.toastMessage || "Ocurrió un error"
          });

          return;
        }

        showSweetAlert({
          icon: response.status,
          title: response.title,
          message: response.alertMessage
        }).then(() => location.reload());
      });
    });

    $(document).on("click", ".btn-add-payment", function() {
      const dataRow = $(this).data("row");

      setTimeout(() => {
        $("#fd-monto").val(dataRow.sale_totalToPay);
        $("#fd-saldo").val(dataRow.sale_balance);
        $("#fd-nuevo-saldo").val(dataRow.sale_balance);
      }, 100);
    });

    $(document).on("click", ".btn-show-products", function() {
      const dataRow = JSON.parse($(this).attr("data-row"));

      setTimeout(() => {
        $("#tabla-ver-productos").html(dataRow.sale_products);
      }, 100);
    });

    $(document).on("click", ".btn-show-pagos", function() {
      const dataRow = JSON.parse($(this).attr("data-row"));

      setTimeout(() => {
        $("#tabla-ver-pagos").html(dataRow.sale_paymentsTable);
      }, 100);
    });
  </script>
</body>

</html>