<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";

$filterDay    = $_GET["filterDay"]    ? $_GET["filterDay"]    : date("d");
$filterMonth  = $_GET["filterMonth"]  ? $_GET["filterMonth"]  : date("m");
$filterYear   = $_GET["filterYear"]   ? $_GET["filterYear"]   : date("Y");

$lastDate     = date("Y-m-d", strtotime($filterYear . "-" . $filterMonth . "-" . $filterDay));
$lastDate     = date("Y-m-d", strtotime($lastDate . " -1 month"));
$lastDay      = date("d", strtotime($lastDate));
$lastMonth    = date("m", strtotime($lastDate));
$lastYear     = date("Y", strtotime($lastDate));

$paymentMethod = $_GET["paymentMethod"] ? $_GET["paymentMethod"] : "";

$months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$page_config = [
  'page_title'        => 'Corte diario',
  'page_identifier'   => 'corte-diario',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

$pageId = $page_config['page_identifier'];

checkModuleActionPermission($pageId, 'ver', true);
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
        <form id="<?= $pageId; ?>-filters-form" class="container-fluid" autocomplete="off">
          <div class="row">
            <div class="col-12">
              <div class="card text-end">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-end p-2 gap-3">
                  <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <div class="form-group m-0">
                      <div class="input-group d-flex align-items-center">
                        <label class="form-label m-0 me-1" for="filter-dateDay">Método de pago:</label>

                        <select id="filter-paymentMethod" class="form-control form-select" name="paymentMethod">
                          <option value="">--Todos--</option>
                          <!-- <option value="efectivo">Efectivo</option>
                          <option value="cheque">Cheque</option>
                          <option value="transferencia">Transferencia</option>
                          <option value="tarjeta_credito">Tarjeta de crédito</option>
                          <option value="tarjeta_debito">Tarjeta de débito</option> -->

                          <option value="efectivo" <?= $paymentMethod == "efectivo" ? "selected" : ""; ?>>Efectivo</option>
                          <option value="cheque" <?= $paymentMethod == "cheque" ? "selected" : ""; ?>>Cheque</option>
                          <option value="transferencia" <?= $paymentMethod == "transferencia" ? "selected" : ""; ?>>Transferencia</option>
                          <option value="tarjeta_debito" <?= $paymentMethod == "tarjeta_debito" ? "selected" : ""; ?>>Tarjeta de débito</option>
                          <option value="tarjeta_credito" <?= $paymentMethod == "tarjeta_credito" ? "selected" : ""; ?>>Tarjeta de crédito</option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="form-group text-end m-0">
                      <div class="input-group d-flex align-items-center">
                        <label class="form-label m-0 me-1" for="filter-dateDay">Fecha:</label>

                        <select id="filter-dateDay" class="form-control form-select" name="filterDay" required>
                          <option value="">Día</option>
                          <?php for ($i = 1; $i <= 31; $i++) : ?>
                            <option value="<?= $i; ?>" <?= $i == $filterDay ? 'selected' : ''; ?>><?= $i; ?></option>
                          <?php endfor; ?>
                        </select>

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
          </div>

          <div id="<?= $pageId; ?>-table" class="row"></div>
        </form>


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