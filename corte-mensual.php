<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";

$filterDay    = date("d");
$filterMonth  = $_GET["filterMonth"]  ? $_GET["filterMonth"]  : date("m");
$filterYear   = $_GET["filterYear"]   ? $_GET["filterYear"]   : date("Y");

$lastDate     = date("Y-m-d", strtotime($filterYear . "-" . $filterMonth . "-" . $filterDay));
$lastDate     = date("Y-m-d", strtotime($lastDate . " -1 month"));
$lastDay      = date("d", strtotime($lastDate));
$lastMonth    = date("m", strtotime($lastDate));
$lastYear     = date("Y", strtotime($lastDate));

$months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$page_config = [
  'page_title'        => 'Corte mensual',
  'page_identifier'   => 'corte-mensual',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);


$todayMonthData               = new stdClass();
$todayMonthData->totalAmount  = getTotalAmountOfMonth($filterMonth, $filterYear);
$todayMonthData->totalSales   = getTotalSalesOfMonth($filterMonth, $filterYear);
$todayMonthData->averageCard  = getAverageOfMonthCards($filterMonth, $filterYear);
$todayMonthData->averageAmountTable = getAverageAmountOfMonthTable($filterYear);
$todayMonthData->averageSalesTable  = getAverageSalesOfMonthTable($filterYear);

$lastMonthData                = new stdClass();
$lastMonthData->totalAmount   = getTotalAmountOfMonth($lastMonth, $lastYear);
$lastMonthData->totalSales    = getTotalSalesOfMonth($lastMonth, $lastYear);
$lastMonthData->averageCard   = getAverageOfMonthCards($lastMonth, $lastYear);
$lastMonthData->averageAmountTable = getAverageAmountOfMonthTable($lastYear);
$lastMonthData->averageSalesTable  = getAverageSalesOfMonthTable($lastYear);


$topFiveSales                 = getTopFiveSales($filterMonth, $filterYear);
$topFiveMostSelledProducts    = getTopFiveMostSelledProducts($filterMonth, $filterYear);
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
          <form id="filters-form" class="row">
            <div class="col-12">
              <div class="card text-end">
                <div class="card-body d-flex justify-content-end p-2">
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="form-group text-end m-0">
                      <div class="input-group d-flex align-items-center">
                        <label class="form-label m-0 me-1" for="filter-dateDay">Fecha:</label>

                        <!-- <select id="filter-dateDay" class="form-control form-select" name="filterDay" required>
                          <option value="">Día</option>
                          <?php for ($i = 1; $i <= 31; $i++) : ?>
                            <option value="<?= $i; ?>" <?= $i == $filterDay ? 'selected' : ''; ?>><?= $i; ?></option>
                          <?php endfor; ?>
                        </select> -->

                        <select id="filter-dateMonth" class="form-control form-select" name="filterMonth" required>
                          <option value="">Mes</option>
                          <?php for ($i = 1; $i <= 12; $i++) : ?>
                            <option value="<?= $i; ?>" <?= $i == $filterMonth ? 'selected' : ''; ?>><?= $months[$i - 1]; ?></option>
                          <?php endfor; ?>
                        </select>

                        <select id="filter-dateYear" class="form-control form-select" name="filterYear" required>
                          <option value="">Año</option>
                          <?php for ($i = 2024; $i <= date("Y"); $i++) : ?>
                            <option value="<?= $i; ?>" <?= $i == $filterYear ? 'selected' : ''; ?>><?= $i; ?></option>
                          <?php endfor; ?>
                        </select>

                        <button class="btn btn-primary">
                          <i class="fa fa-search"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>

          <div class="row">
            <div class="col-12 col-lg-3">
              <div class="row">
                <div class="col-12">
                  <div class="card card-border-top border-top-success">
                    <div class="card-body d-flex flex-row align-items-center justify-content-between">
                      <div>
                        <div class="avatar-lg">
                          <div class="avatar-title border-1 bg-light rounded-circle">
                            <i class="fa fa-dollar-sign text-success fa-2x"></i>
                          </div>
                        </div>
                      </div>

                      <div class="text-end d-flex flex-column gap-1">
                        <h4 class="m-0">Total mensual</h4>
                        <h5 class="m-0 text-muted">$<?= number_format($todayMonthData->totalAmount, DECIMALS_CURRENCY); ?></h5>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-12">
                  <div class="card card-border-top border-top-info">
                    <div class="card-body d-flex flex-row align-items-center justify-content-between">
                      <div>
                        <div class="avatar-lg">
                          <div class="avatar-title border-1 bg-light rounded-circle">
                            <i class="fa fa-chart-line text-info fa-2x"></i>
                          </div>
                        </div>
                      </div>

                      <div class="text-end d-flex flex-column gap-1">
                        <h4 class="m-0">Promedio</h4>
                        <h5 class="m-0 text-muted">$<?= number_format($todayMonthData->averageCard, DECIMALS_CURRENCY); ?></h5>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="col-12">
                  <div class="card card-border-top border-top-warning">
                    <div class="card-body d-flex flex-row align-items-center justify-content-between">
                      <div>
                        <div class="avatar-lg">
                          <div class="avatar-title border-1 bg-light rounded-circle">
                            <i class="fa fa-chart-line text-warning fa-2x"></i>
                          </div>
                        </div>
                      </div>

                      <div class="text-end d-flex flex-column gap-1">
                        <h3 class="m-0">Envíos</h3>
                        <h4 class="m-0 text-muted">0</h4>
                      </div>
                    </div>
                  </div>
                </div> -->
              </div>
            </div>

            <div class="col-12 col-lg-9">
              <div class="card card-border-top">
                <div class="card-body">
                  <h4 class="header-title mb-3">Comparación ventas</h4>

                  <div class="table-responsive">
                    <table class="table table-hover">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th class="text-end">Total mensual</th>
                          <th class="text-end">Mes anterior</th>
                          <th class="text-end">Promedio</th>
                        </tr>
                      </thead>

                      <tbody>
                        <tr>
                          <th>Monto</th>

                          <td class="text-end">$<?= number_format($todayMonthData->totalAmount, DECIMALS_CURRENCY); ?></td>
                          <td class="text-end">$<?= number_format($lastMonthData->totalAmount, DECIMALS_CURRENCY); ?></td>
                          <td class="text-end">$<?= number_format($todayMonthData->averageAmountTable, DECIMALS_CURRENCY); ?></td>
                        </tr>

                        <tr>
                          <th>Ventas</th>

                          <td class="text-end"><?= format_decimal_number($todayMonthData->totalSales, DECIMALS_CURRENCY); ?></td>
                          <td class="text-end"><?= format_decimal_number($lastMonthData->totalSales, DECIMALS_CURRENCY); ?></td>
                          <td class="text-end"><?= format_decimal_number($todayMonthData->averageSalesTable, DECIMALS_CURRENCY); ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-12 col-lg-6">
                  <div class="card card-border-top">
                    <div class="card-body">
                      <h4 class="header-title mb-3">Top 5 ventas</h4>

                      <?php if (sizeof($topFiveSales) == 0) : ?>
                        <p>No hay datos para mostrar</p>
                      <?php endif; ?>

                      <?php if (sizeof($topFiveSales) > 0) : ?>
                        <div class="table-responsive">
                          <table class="table table-hover">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Folio</th>
                                <th class="text-end">Monto</th>
                              </tr>
                            </thead>

                            <tbody>
                              <?php foreach ($topFiveSales as $key => $customer) : ?>
                                <tr>
                                  <td><?= $key + 1; ?></td>
                                  <td><?= $customer->name; ?></td>
                                  <td><?= $customer->saleFolio; ?></td>
                                  <td class="text-end">$<?= number_format($customer->totalAmount, DECIMALS_CURRENCY); ?></td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>

                <div class="col-12 col-lg-6">
                  <div class="card card-border-top">
                    <div class="card-body">
                      <h4 class="header-title mb-3">Top 5 productos vendidos</h4>

                      <?php if (sizeof($topFiveMostSelledProducts) == 0) : ?>
                        <p>No hay datos para mostrar</p>
                      <?php endif; ?>

                      <?php if (sizeof($topFiveMostSelledProducts) > 0) : ?>
                        <div class="table-responsive">
                          <table class="table table-hover">
                            <thead>
                              <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Monto</th>
                              </tr>
                            </thead>

                            <tbody>
                              <?php foreach ($topFiveMostSelledProducts as $key => $product) : ?>
                                <tr>
                                  <td><?= $key + 1; ?></td>
                                  <td><?= $product->name; ?></td>
                                  <td class="text-center"><?= format_decimal_number($product->totalSold . 2); ?></td>
                                  <td class="text-end">$<?= number_format($product->totalAmount, DECIMALS_CURRENCY); ?></td>
                                </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
  <script src="<?= BASE_URL; ?>/src/plugins/datatable/datatable.js"></script>

  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/main.js"></script>
  <script src="<?= BASE_URL; ?>/src/plugins/multidatatable/init.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>
</body>

</html>