<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";

$page_config = [
  'page_title'        => 'Ventas pagos',
  'page_identifier'   => 'ventas-pagos',
  'modal_title_add'   => 'Nuevo pago',
  'modal_title_edit'  => 'Editar pago'
];

$saleFolio = cleanStr($_GET["uid"]);

if (!$saleFolio) {
  header("Location: " . BASE_URL . "/ventas");
  exit;
}

$saleData = get_sale_data_by_folio($saleFolio);

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$totalPaid  = getSaleTotalPaidById($saleData->id);
$totalToPay = getSaleTotalById($saleData->id);
$balance    = round($totalToPay - $totalPaid, DECIMALS_CURRENCY);

$invoice = getSaleInvoiceBySaleIdAndType($saleData->id, "ingreso");
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
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <!-- Logo & title -->
                  <div class="clearfix">
                    <div class="float-start">
                    </div>

                    <div class="float-end">
                      <h4 class="m-0 d-print-none">Venta</h4>
                    </div>
                  </div>

                  <div class="row">
                    <div class="col-md-6">
                      <div class="mt-3">
                        <p><b>Gestión de Pagos</b></p>
                        <p class="text-muted">Administra los pagos correspondientes a esta venta. Aquí puedes registrar y consultar todos los pagos realizados por el cliente.</p>
                      </div>
                    </div><!-- end col -->
                    <div class="col-md-4 offset-md-2">
                      <div class="mt-3 float-end">
                        <p class="m-b-10"><strong>Folio: </strong> <span class="float-end"><?= $saleData->folio; ?></span></p>
                        <p class="m-b-10"><strong>Fecha: </strong> <span class="float-end"> &nbsp;&nbsp;&nbsp;&nbsp; <?= $saleData->date_format; ?></span></p>
                        <p class="m-b-10"><strong>Estado de la venta: </strong> <span class="float-end">
                            <?php if ($saleData->status == 'activo'): ?>
                              <span class="badge bg-success">Activa</span>
                            <?php else: ?>
                              <span class="badge bg-danger">Cancelada</span>
                            <?php endif; ?>
                          </span></p>
                        <p class="m-b-10"><strong>Pago : </strong> <span class="float-end">
                            <?php if (isset($saleData->paid) && $saleData->paid == 'si'): ?>
                              <span class="badge bg-success">Completado</span>
                            <?php else: ?>
                              <span class="badge bg-warning">Pendiente</span>
                            <?php endif; ?>
                          </span></p>
                      </div>
                    </div><!-- end col -->
                  </div>
                  <!-- end row -->

                  <div class="row mt-3">
                    <div class="col-lg-4">
                      <h5>Información del Cliente</h5>
                      <address>
                        <?= $saleData->customer->name; ?><br>
                        RFC: <?= $saleData->customer->rfc; ?><br>
                        Email: <?= $saleData->customer->email; ?><br>
                        <abbr title="Phone">Tel:</abbr> <?= $saleData->customer->phone ? formatPhoneNumber($saleData->customer->phone) : "--"; ?>
                      </address>
                    </div> <!-- end col -->

                    <div class="col-lg-4">
                      <h5>Información de la Sucursal</h5>
                      <address>
                        <?= $saleData->branch->name; ?><br>
                        <?= $saleData->branch->address ?? 'Dirección no disponible'; ?><br>
                        <strong>Vendedor:</strong> <?= $saleData->seller->name; ?><br>
                        Email: <?= $saleData->seller->email; ?>
                      </address>
                    </div> <!-- end col -->

                    <div class="col-lg-4">
                      <h5>Resumen de la venta</h5>
                      <address>
                        <span class="fw-bold">Monto:</span> $<?= number_format($totalToPay, DECIMALS_CURRENCY_TICKET); ?><br>
                        <span class="fw-bold">Total abonado:</span> $<?= number_format($totalPaid, DECIMALS_CURRENCY_TICKET); ?><br>
                        <span class="fw-bold">Saldo:</span> $<?= number_format($balance, DECIMALS_CURRENCY_TICKET); ?>
                      </address>
                    </div> <!-- end col -->
                  </div>
                  <!-- end row -->

                  <form id="<?= $page_config['page_identifier']; ?>-filters-form" autocomplete="off">
                    <div class="row">
                      <div class="col-12">
                        <div class="d-flex align-items-center w-100 flex-lg-row gap-2">
                          <div class="flex-1 w-100 d-flex align-items-center gap-1">
                            <!-- <div class="flex-1" style="max-width: 14rem;">
                            <div class="form-group">
                              <label class="form-label" for="filter-fecha">Fecha</label>
                              <input id="filter-fecha" class="form-control datepicker" name="fecha" value="" type="text">
                            </div>
                          </div> -->
                          </div>

                          <div>
                            <?php if ($saleData->paid == 'no') : ?>
                              <div class="dropdown">
                                <div class="btn-group">
                                  <?= getFilterActions($page_config['page_identifier'], [
                                    "sale_totalToPay" => $totalToPay,
                                    "sale_balance"    => $balance,
                                    "sale_newBalance" => $totalToPay
                                  ]); ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>

                        <input name="sale_id" value="<?= md5($saleData->id); ?>" type="hidden">
                      </div>
                    </div>

                    <div class="row">
                      <div id="<?= $page_config['page_identifier']; ?>-table" class="col-12 p-0"></div>
                    </div>
                  </form>
                  <!-- end row -->

                  <div class="row">
                    <div class="col-sm-6">
                      <div class="clearfix pt-5">
                        <h6 class="text-muted">Observaciones:</h6>
                        <small class="text-muted">
                          <?= !empty($saleData->observations) ? $saleData->observations : 'Sin observaciones adicionales.'; ?>
                        </small>
                      </div>
                    </div> <!-- end col -->
                  </div>
                  <!-- end row -->

                  <div class="mt-4 mb-1">
                    <div class="text-end d-print-none">
                      <a href="javascript:window.print()" class="btn btn-primary waves-effect waves-light me-1">
                        <i class="mdi mdi-printer me-1"></i> Imprimir
                      </a>
                      <a href="<?= BASE_URL; ?>/ventas" class="btn btn-secondary waves-effect waves-light me-1">
                        <i class="mdi mdi-arrow-left me-1"></i> Volver a Ventas
                      </a>
                      <!-- Aquí se agregarán los botones de gestión de pagos -->
                    </div>
                  </div>
                </div>
              </div> <!-- end card -->
            </div> <!-- end col -->
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

  <!-- JQUERY UI -->
  <script src="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.js"></script>

  <!-- DATEPICKER SPANISH -->
  <script src="<?= BASE_URL; ?>/src/plugins/datepicker-spanish/datepicker-spanish.js"></script>

  <!-- MULTITABLE JS -->
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <!-- validate js -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <script>
    $('#filter-fecha').on('change', () => load(1, PAGE_CONFIG.page_identifier));

    $(".btn-add").on("click", function() {
      const dataRow = $(this).data("row");

      setTimeout(() => {
        $("#fd-monto").val(dataRow.sale_totalToPay);
        $("#fd-saldo").val(dataRow.sale_balance);
        $("#fd-nuevo-saldo").val(dataRow.sale_balance);
      }, 100);
    });
  </script>
</body>

</html>