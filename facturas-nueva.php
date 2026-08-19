<?php
include "inc/session.inc.php";

require_once __DIR__ . "/data/lib/helpers/emisores.helpers.php";
require_once __DIR__ . "/data/lib/helpers/g-catalogs.helpers.php";
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";
require_once __DIR__ . "/data/lib/helpers/customers.helper.php";
include 'data/lib/helpers/catalogs.helper.php';

$page_config = [
  'page_identifier'   => 'facturas/nueva',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

$pcModule = checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
$page_config['page_title'] = $pcModule->name;

$invoiceType        = cleanStr($_GET['tipo_factura'] ?? "ingreso");
$salesFolio         = cleanStr($_GET['folio']);
$saleUids           = cleanStr($_GET['uids']);
$md5SalePaymentid   = cleanStr($_GET["pago"]);

$canInvoice         = $invoiceType ? true : false;
$defaultCustomerId  = 1;

$invoiceAbreviation = INVOICE_TYPES_ABREVIATIONS[$invoiceType];

$isInvoiceIncome    = $invoiceAbreviation == 'I' ? true : false;
$isInvoicePayment   = $invoiceAbreviation == 'P' ? true : false;
$isInvoiceDischarge = $invoiceAbreviation == 'E' ? true : false;

$emisoresHelper   = new EmisoresHelper();
$gCatalogsHelper  = new GCatalogsHelpers();

$sale         = null;
$invoicedSale = false;

if ($salesFolio) :
  $sale = get_sale_data_by_folio($salesFolio);

  if ($sale) :
    // Formas de pago basado en la que tiene la mayor cantidad
    $efectivo_amount         = floatval($sale->efectivo);
    $cheque_amount           = floatval($sale->cheque);
    $transferencia_amount    = floatval($sale->transferencia);
    $tarjeta_credito_amount  = floatval($sale->tarjeta_credito);
    $tarjeta_debito_amount   = floatval($sale->tarjeta_debito);

    // Saber cuál es el método de pago con mayor cantidad
    $payment_methods = [
      'Efectivo'            => $efectivo_amount,
      'Cheque'              => $cheque_amount,
      'Transferencia'       => $transferencia_amount,
      'Tarjeta de crédito'  => $tarjeta_credito_amount,
      'Tarjeta de débito'   => $tarjeta_debito_amount
    ];

    // Ordenar los métodos del más alto al más bajo
    arsort($payment_methods);

    $firstMethod = key($payment_methods);

    // Definir la forma de pago basada en el que tiene la mayor cantidad
    $paymentFormId = null;

    switch ($firstMethod) {
      case 'Efectivo':
        $paymentFormId = 1;
        break;
      case 'Cheque':
        $paymentFormId = 2;
        break;
      case 'Transferencia':
        $paymentFormId = 3;
        break;
      case 'Tarjeta de crédito':
        $paymentFormId = 4;
        break;
      case 'Tarjeta de débito':
        $paymentFormId = 18;
        break;
      default:
        $paymentFormId = 1;
        break;
    }

    // Verificar si la venta ya está facturada
    $invoice = getIncomeInvoiceBySaleId($sale->id);

    if ($invoice) $sale = null;

    if (!$invoice) {
      $products = [];

      foreach ($sale->list as $item) :
        if ($item->cancelled) continue;

        $discount_percent = $item->cart_sale_discount;
        $discount_amount  = ($item->cart_sale_price * $item->cart_quantity) * ($discount_percent / 100);

        $products[] = [
          "productId"             => "{$item->id}",
          "productName"           => $item->name,
          "unitMeasurementId"     => "{$item->unitMeasurementId}",
          "unitMeasurementName"   => $item->unitMeasurementName,
          "keyProductServiceId"   => "{$item->keyProductServiceId}",
          "keyProductServiceName" => $item->keyProductServiceName,
          "quantity"              => $item->cart_quantity,
          "unitPrice"             => $item->cart_sale_price,
          "amount"                => $item->cart_sale_amount,
          "discount"              => $discount_amount,
          "taxObject"             => $item->have_iva == "si" ? "02" : "01",
          "haveIVA"               => $item->have_iva == "si" ? true : false,
          "haveIEPS"              => (($item->have_ieps ?? "no") == "si") ? true : false,
          "iepsPercentage"        => (float)($item->ieps_percentage ?? $item->ieps_porcentaje ?? 0),
          "iepsCurrency"          => (float)($item->cart_sale_total_ieps ?? $item->ieps ?? 0),
          "serialNumbers"         => $item->serial_numbers,
          "comments"              => $item->comments ?? ""
        ];
      endforeach;
    }
  endif;
endif;

if ($saleUids) {
  $saleUidsArrayRaw   = explode(",", $saleUids);
  $saleUidsArray      = [];

  foreach ($saleUidsArrayRaw as $saleUidRaw) {
    $saleUidRaw = trim((string)$saleUidRaw);

    if ($saleUidRaw === "" || $saleUidRaw === "0" || strtoupper($saleUidRaw) === "NULL") continue;
    if (!ctype_digit($saleUidRaw)) continue;

    $saleUidsArray[] = (int)$saleUidRaw;
  }

  $saleUidsArray = array_values(array_unique($saleUidsArray));
  sort($saleUidsArray, SORT_NUMERIC);

  $salesForInvoicing  = getSalesForInvoicingBySaleIds($saleUidsArray);
  $mixedTotals        = $salesForInvoicing["mixedTotals"];

  // Definir el método de pago
  // Formas de pago basado en la que tiene la mayor cantidad
  $efectivo_amount         = floatval($mixedTotals["totalCash"]);
  $cheque_amount           = floatval($mixedTotals["totalCheque"]);
  $transferencia_amount    = floatval($mixedTotals["totalTransfer"]);
  $tarjeta_credito_amount  = floatval($mixedTotals["totalCreditCard"]);
  $tarjeta_debito_amount   = floatval($mixedTotals["totalDebitCard"]);

  // Saber cuál es el método de pago con mayor cantidad
  $payment_methods = [
    'Efectivo'            => $efectivo_amount,
    'Cheque'              => $cheque_amount,
    'Transferencia'       => $transferencia_amount,
    'Tarjeta de crédito'  => $tarjeta_credito_amount,
    'Tarjeta de débito'   => $tarjeta_debito_amount
  ];

  arsort($payment_methods);

  $firstMethod = key($payment_methods);

  // Definir la forma de pago basada en el que tiene la mayor cantidad
  $paymentFormId = null;

  switch ($firstMethod) {
    case 'Efectivo':
      $paymentFormId = 1;
      break;
    case 'Cheque':
      $paymentFormId = 2;
      break;
    case 'Transferencia':
      $paymentFormId = 3;
      break;
    case 'Tarjeta de crédito':
      $paymentFormId = 4;
      break;
    case 'Tarjeta de débito':
      $paymentFormId = 18;
      break;
    default:
      $paymentFormId = 1;
      break;
  }

  //echo "<pre>";
  //echo $paymentFormId;
  //echo "<br>";
  //echo json_encode($salesForInvoicing, JSON_PRETTY_PRINT);
  //echo "</pre>";
  //die;

  if ($salesForInvoicing["status"] == "error") $canInvoice = false;

  $products = [];
  $sales = $salesForInvoicing["sales"];

  foreach ($sales as $rowSale) {
    $products[] = [
      "saleFolio"             => $rowSale["saleFolio"],
      "productId"             => $rowSale["productId"],
      "productName"           => $rowSale["productSku"],
      "unitMeasurementId"     => $rowSale["satUnitKeyId"],
      "unitMeasurementName"   => $rowSale["satUnitKeyName"],
      "keyProductServiceId"   => $rowSale["satProductServiceKeyId"],
      "keyProductServiceName" => $rowSale["satProductServiceKeyName"],
      "quantity"              => 1,
      "unitPrice"             => $rowSale["price"],
      "amount"                => $rowSale["price"],
      "discount"              => 0,
      "taxObject"             => $rowSale["iva"] > 0 ? "02" : "01",
      "haveIVA"               => $rowSale["iva"] > 0 ? true : false,
      "ivaCurrency"           => $rowSale["iva"],
      "haveIEPS"              => (($rowSale["ieps"] ?? 0) > 0) ? true : false,
      "iepsPercentage"        => (float)($rowSale["iepsPercentage"] ?? $rowSale["iepsPercentage"] ?? 0),
      "iepsCurrency"          => (float)($rowSale["ieps"] ?? 0),
      "serialNumbers"         => []
    ];
  }
}

// echo "<pre>";
// echo json_encode($products, JSON_PRETTY_PRINT);
// echo "</pre>";
// die;

if ($isInvoicePayment) $sale = null;

$defaultCustomer = null;

if (!$sale && $defaultCustomerId > 0) {
  $defaultCustomer = customer_get_by_id($defaultCustomerId);
}

$customerFormId          = $sale ? $sale->customer->id : ($defaultCustomer->id ?? "");
$customerFormName        = $sale ? $sale->customer->name : ($defaultCustomer->name ?? "");
$customerBusinessName    = $sale ? $sale->customer->business_name : ($defaultCustomer->businessName ?? "");
$customerRFC             = $sale ? $sale->customer->rfc : ($defaultCustomer->rfc ?? "");
$customerTaxRegimeId     = $sale ? $sale->customer->fiscal_regime : ($defaultCustomer->taxRegimeId ?? "");
$customerTaxResidence    = $sale ? $sale->customer->fiscal_address : ($defaultCustomer->taxResidence ?? "");
$customerEmail           = $sale ? $sale->customer->email : ($defaultCustomer->email ?? "");

$user_data        = getUserData(get_id_usuario());
$IS_ADMIN         = $user_data['IS_ADMIN'] === 'si' ? true : false;
$basicInfoCoSize  = "col-12 col-md-6 col-lg-4";

if ($IS_ADMIN) $basicInfoCoSize = "col-12 col-md-6 col-lg-3";

$userBranchOfficeId = getSessionBranchOfficeId();
$userBranchOfficeData = getBranchOfficeData($userBranchOfficeId);

if ($md5SalePaymentid) {
  $salePaymentData = getSalePaymentByMd5Id($md5SalePaymentid);

  // Obtener la venta
  $saleFromPayment = getSaleById($salePaymentData["id_venta"]);

  $customerData = customer_get_by_id($saleFromPayment["id_cliente"]);

  $customerFormId          = $customerData->id ?? "";
  $customerFormName        = $customerData->name ?? "";
  $customerBusinessName    = $customerData->businessName ?? "";
  $customerRFC             = $customerData->rfc ?? "";
  $customerTaxRegimeId     = $customerData->taxRegimeId ?? "";
  $customerTaxResidence    = $customerData->taxResidence ?? "";
  $customerEmail           = $customerData->email ?? "";

  // Obtener la factura de la venta de tipo PPD
  $invoiceFromPayment = getSaleInvoiceBySaleIdAndType($saleFromPayment["id_venta"], "ingreso");

  if (!$salePaymentData) {
    closeSession();
    die;
  }

  $invoicePaymentData = getSalePaymentInvoicePaymetData($invoiceFromPayment["id_factura"]);

  $payment_monto                  = $salePaymentData["monto_total"]               ?? 0;
  $payment_num_parcialidad        = $invoicePaymentData["num_parcialidad"]        ?? 1;
  $payment_importe_saldo_anterior = $invoicePaymentData["importe_saldo_insoluto"] ?? $saleFromPayment["total"];
  $payment_importe_saldo_insoluto = $invoicePaymentData["importe_saldo_insoluto"] ? ($invoicePaymentData["importe_saldo_insoluto"] - $salePaymentData["monto_total"]) : ($saleFromPayment["total"] - $salePaymentData["monto_total"]);

  $payment_monto                  = (float) round($payment_monto, 2);
  $payment_num_parcialidad        = (int) round($payment_num_parcialidad, 0);
  $payment_importe_saldo_anterior = (float) round($payment_importe_saldo_anterior, 2);
  $payment_importe_saldo_insoluto = (float) round($payment_importe_saldo_insoluto, 2);

  // echo $payment_monto;
  // echo "<br>";
  // echo $payment_num_parcialidad;
  // echo "<br>";
  // echo $payment_importe_saldo_anterior;
  // echo "<br>";
  // echo $payment_importe_saldo_insoluto;
  // die;

  // echo json_encode($invoicePaymentData);
  // die;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <?php include 'src/components/head.php'; ?>

  <!-- JQUERYUI -->
  <link rel="stylesheet" href="<?= BASE_URL; ?>/src/plugins/jquery-ui/jquery-ui.min.css">

  <!-- SELECT2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

<body class="loading" data-layout='<?= $minton_layout; ?>'>
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
            "pageTitle"       => "Nueva factura",
            "pageDescription" => "Crea una nueva factura para tu sucursal"
          ]); ?>

          <div class="row">
            <div class="col-12">
              <form id="invoice-form" class="form-validate card" autocomplete="off">
                <div class="card-body">
                  <?php if ($saleUids) : ?>
                    <?php if ($salesForInvoicing["status"] == "success") : ?>
                      <div class="alert alert-success">
                        <h3 class="header-title">Facturando múltiples ventas</h3>
                        Se cargaron los datos de las ventas seleccionadas para facturar.
                      </div>
                    <?php endif; ?>

                    <?php if ($salesForInvoicing["status"] == "error") : ?>
                      <div class="alert alert-danger d-flex flex-column gap-2 flex-lg-row align-items-lg-center justify-content-lg-between">
                        <div>
                          <h3 class="header-title">Error al procesar las ventas</h3>
                          <?= $salesForInvoicing["message"]; ?>
                        </div>

                        <div>
                          <a class="btn btn-danger" href="<?= BASE_URL; ?>/ventas">Ir a las ventas</a>
                        </div>
                      </div>
                    <?php endif; ?>

                    <input name="isMultipleSales" value="si" type="hidden">
                    <input name="saleUids" value="<?= $saleUids; ?>" type="hidden">
                  <?php endif; ?>

                  <?php if ($isInvoiceIncome || $sale) : ?>
                    <?php if (!$saleUids) : ?>
                      <div class="row">
                        <div class="col-12">
                          <input id="alerta" class="check-with-content" name="alerta" value="si" type="checkbox" <?= $salesFolio ? "checked" : false; ?>>
                          <label for="alerta" class="form-label label-check">Cargar datos de una venta</label>

                          <div class="content-check pt-1">
                            <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fa fa-dollar-sign me-1"></i> Información de venta</h5>

                            <div class="row">
                              <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-group">
                                  <label class="form-label" for="fdf-folioVenta">Folio</label>
                                  <div class="input-group">
                                    <input id="fdf-folioVenta" class="form-control" name="folioVenta" value="<?= $_GET["folio"]; ?>" type="text">

                                    <button id="btn-add-folio" class="btn btn-secondary" type="button">Agregar</button>
                                  </div>
                                </div>
                              </div>

                              <?php if ($sale) : ?>
                                <input name="idVenta" value="<?= $sale->id; ?>" type="hidden">
                              <?php endif; ?>

                              <?php if ($salesFolio) : ?>
                                <?php if (!$sale && !$invoice) : ?>
                                  <div class="col-12 col-md-6 col-lg-8">
                                    <div class="alert alert-danger mt-md-3" role="alert">
                                      <i class="fa fa-exclamation-triangle me-1"></i> No se encontró la venta con el folio <strong><?= $salesFolio; ?></strong>
                                    </div>
                                  </div>
                                <?php endif; ?>

                                <?php if ($invoice) : ?>
                                  <div class="col-12 col-md-6 col-lg-8">
                                    <div class="alert alert-info mt-md-3" role="alert">
                                      <i class="fa fa-exclamation-triangle me-1"></i> La venta con el folio <strong><?= $salesFolio; ?></strong> ya fue facturada.
                                    </div>
                                  </div>
                                <?php endif; ?>
                              <?php endif; ?>

                              <?php if ($salesFolio && $sale) : ?>
                                <div class="col-12 col-md-6 col-lg-8">
                                  <div class="alert alert-success mt-md-3" role="alert">
                                    <i class="fa fa-check me-1"></i> Se cargaron los datos de la venta con el folio <strong><?= $salesFolio; ?></strong>
                                  </div>
                                </div>
                              <?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>

                  <?php if ($salePaymentData) : ?>
                    <input name="idVentaPago" value="<?= $salePaymentData["id_venta_pago"]; ?>" type="hidden">
                  <?php endif; ?>

                  <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fa fa-list me-1"></i> Información básica</h5>

                  <div class="row">
                    <div class="<?= $basicInfoCoSize; ?>">
                      <div class="form-group">
                        <label class="form-label" for="fdf-tipo_factura-label">Tipo de factura<span class="text-danger">*</span></label>

                        <select id="fdf-tipo_factura-label" class="form-control form-select" name="tipo_factura_label" required>
                          <?php
                          $invoiceTypes = [
                            [
                              "id"    => "ingreso",
                              "label" => "I Ingreso",
                              "selected" => $invoiceType == "ingreso" ? true : false,
                              "visible" => ((!$sale) || ($sale && $invoiceType == "ingreso")) ? true : false
                            ],
                            [
                              "id"    => "pago",
                              "label" => "P Pago",
                              "selected" => $invoiceType == "pago" ? true : false,
                              "visible" => ((!$sale) || ($sale && $invoiceType == "pago")) ? true : false
                            ],
                            [
                              "id"    => "anticipo-de-compra",
                              "label" => "I Anticipo de compra",
                              "selected" => $invoiceType == "anticipo-de-compra" ? true : false,
                              "visible" => ((!$sale) || ($sale && $invoiceType == "anticipo-de-compra")) ? true : false
                            ],
                            [
                              "id"    => "nota-de-credito",
                              "label" => "E Nota de crédito",
                              "selected" => $invoiceType == "nota-de-credito" ? true : false,
                              "visible" => ((!$sale) || ($sale && $invoiceType == "nota-de-credito")) ? true : false
                            ]
                          ];
                          ?>

                          <?php foreach ($invoiceTypes as $type) : ?>
                            <?php if ($type['visible']) : ?>
                              <option value="<?= $type['id']; ?>" <?= $type['selected'] ? "selected" : ""; ?>><?= $type['label']; ?></option>
                            <?php endif; ?>
                          <?php endforeach; ?>

                          <!-- <option value="ingreso" <?= $invoiceType == "ingreso" ? "selected" : ""; ?>>I Ingreso</option>
                          <option value="pago" <?= $invoiceType == "pago" ? "selected" : ""; ?>>P Pago</option>
                          <option value="anticipo-de-compra" <?= $invoiceType == "anticipo-de-compra" ? "selected" : ""; ?>>I Anticipo de compra</option>
                          <option value="nota-de-credito" <?= $invoiceType == "nota-de-credito" ? "selected" : ""; ?>>E Nota de crédito</option> -->
                        </select>
                      </div>
                    </div>

                    <input name="tipo_factura" value="<?= $invoiceAbreviation; ?>" type="hidden">

                    <?php if ($canInvoice) : ?>
                      <?php if ($IS_ADMIN) : ?>
                        <div class="<?= $basicInfoCoSize; ?>">
                          <div class="form-group">
                            <label class="form-label" for="fdf-id_sucursal">Sucursal<span class="text-danger">*</span></label>

                            <select id="fdf-id_sucursal" class="form-control form-select" name="id_sucursal" required>
                              <?= getBranchOfficesCatalog($sale ? $sale->branch_id : 1); ?>
                            </select>
                          </div>
                        </div>
                      <?php endif; ?>

                      <div class="<?= $basicInfoCoSize; ?>">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_emisor">Emisor<span class="text-danger">*</span></label>

                          <?php
                          // Obtener el primer emisor
                          $query        = "SELECT id_emisor FROM {$db_dti}_emisores WHERE status = 'activo' ORDER BY nombre_razon_social ASC LIMIT 1";
                          $result       = mysqli_query($mysqli, $query);
                          $firstEmisor  = mysqli_fetch_assoc($result);
                          ?>
                          <select id="fdf-id_emisor" class="form-control form-select" name="id_emisor" required>
                            <option value="">--Seleccionar--</option>
                            <?= $emisoresHelper->getCatalog($firstEmisor['id_emisor']); ?>
                          </select>
                        </div>
                      </div>

                      <div class="<?= $basicInfoCoSize; ?>">
                        <div class="form-group">
                          <label class="form-label" for="fdf-fecha_emision">Fecha de emisión<span class="text-danger">*</span></label>

                          <input id="fdf-fecha_emision" class="form-control datepicker" name="fecha_emision" value="<?= date("d-m-Y"); ?>" type="text" required>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if ($canInvoice) : ?>
                    <?php if ($saleUids) : ?>
                      <!-- START INFORMACIÓN GLOBAL -->
                      <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fa fa-globe me-1"></i> Información global</h5>

                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label class="form-label" for="fdf-fg_periodicidad">Periodicidad</label>
                            <select id="fdf-fg_periodicidad" class="form-control form-select" name="fg_periodicidad" required>
                              <option value="">--Seleccionar--</option>
                              <?php foreach (FACTURA_GLOBAL_PERIODICIDAD as $periodicidadId => $periodicidadLabel) :
                                $selected = $periodicidadId == "01" ? "selected" : "";
                              ?>
                                <option value="<?= $periodicidadId; ?>" <?= $selected; ?>><?= $periodicidadLabel; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label class="form-label" for="fdf-fg_meses">Meses</label>
                            <select id="fdf-fg_meses" class="form-control form-select" name="fg_meses" required>
                              <option value="">--Seleccionar--</option>
                              <?php foreach (FACTURA_GLOBAL_MESES as $mesId => $mesLabel) :
                                $todayMonth = date('m');
                                $selected = ($mesId == $todayMonth && $mesId <= 12) ? "selected" : "";
                              ?>
                                <option value="<?= $mesId; ?>" <?= $selected; ?>><?= $mesLabel; ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label class="form-label" for="fdf-fg_anio">Año</label>
                            <input id="fdf-fg_anio" class="form-control number-input" name="fg_anio" placeholder="YYYY" pattern="202[1-9]|20[3-9][0-9]" title="Debe ser un año de 4 dígitos (mínimo 2021)" maxlength="4" value="<?= date('Y'); ?>" type="text" required>
                          </div>
                        </div>
                      </div>

                      <script>
                        document.addEventListener('DOMContentLoaded', function() {
                          const selectPeriodicidad = document.getElementById('fdf-fg_periodicidad');
                          const selectMeses = document.getElementById('fdf-fg_meses');
                          const opcionesMeses = selectMeses.querySelectorAll('option');

                          selectPeriodicidad.addEventListener('change', function() {
                            const periodicidad = this.value;

                            // Resetear el select de meses
                            selectMeses.value = "";

                            opcionesMeses.forEach(option => {
                              const val = parseInt(option.value);

                              // Si no tiene valor (el --Seleccionar--), siempre se muestra
                              if (!option.value) {
                                option.style.display = 'block';
                                return;
                              }

                              if (periodicidad === '05') {
                                // Si es Bimestral, mostrar claves 13 a 18
                                option.style.display = (val >= 13 && val <= 18) ? 'block' : 'none';
                              } else if (periodicidad !== "") {
                                // Si es Diario, Semanal, Quincenal o Mensual, mostrar 01 a 12
                                option.style.display = (val >= 1 && val <= 12) ? 'block' : 'none';
                              } else {
                                // Si no hay periodicidad seleccionada, ocultar todo menos el default
                                option.style.display = 'none';
                              }
                            });
                          });
                        });
                      </script>
                      <!-- END FACTURACIÓN GLOBAL -->
                    <?php endif; ?>

                    <!-- START FACTURAR A -->
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="far fa-user me-1"></i> Facturar a</h5>

                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_cliente">Cliente<span class="text-danger">*</span></label>

                          <div class="input-group" style="flex-wrap: nowrap;">
                            <select id="fdf-id_cliente" class="form-control form-select select2" name="id_cliente" required>
                              <option value="">--Seleccionar--</option>
                            </select>

                            <div class="input-group-text p-0 overflow-hidden" data-bs-toggle="tooltip" data-bs-placement="top" title="Agregar nuevo cliente">
                              <button class="btn btn-secondary rounded-0" type="button" data-bs-toggle="modal" data-bs-target="#clientes-modal">+</button>
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-razon_social">Razón social<span class="text-danger">*</span></label>
                          <input id="fdf-razon_social" class="form-control" name="razon_social" value="<?= $customerBusinessName; ?>" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-cliente_rfc">RFC<span class="text-danger">*</span></label>
                          <input id="fdf-cliente_rfc" class="form-control" name="cliente_rfc" value="<?= $customerRFC; ?>" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_regimen_fiscal">Régimen fiscal<span class="text-danger">*</span></label>

                          <select id="fdf-id_regimen_fiscal" class="form-control form-select" name="id_regimen_fiscal" required>
                            <option value="">--Seleccionar--</option>
                            <?= $gCatalogsHelper->getTaxRegime($customerTaxRegimeId); ?>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-3">
                        <?php
                        $customerCP = $customerTaxResidence;

                        if (!$IS_ADMIN && $sale && $sale->customer->rfc === "XAXX010101000") {
                          $customerCP = $userBranchOfficeData["cp"];
                        }

                        if ($IS_ADMIN && $sale && $sale->customer->rfc === "XAXX010101000") {
                          $userBranchOfficeData = getBranchOfficeData($sale->branch_id);
                          $customerCP = $userBranchOfficeData["cp"];
                        }
                        ?>
                        <div class="form-group">
                          <label class="form-label" for="fdf-cliente_domicilio_fiscal">Domicilio fiscal<span class="text-danger">*</span></label>
                          <input id="fdf-cliente_domicilio_fiscal" class="form-control" name="cliente_domicilio_fiscal" value="<?= $customerCP; ?>" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_uso_cfdi">Uso de CFDI<span class="text-danger">*</span></label>

                          <select id="fdf-id_uso_cfdi" class="form-control form-select" name="id_uso_cfdi" required>
                            <option value="">--Seleccionar--</option>
                            <?php
                            if ($defaultCustomerId == 1) $uso_cfdi_id = 22;
                            ?>

                            <?php if ($isInvoiceIncome or $isInvoiceDischarge) : ?>
                              <?= $gCatalogsHelper->getCFDICatalog($sale ? $sale->cfdi_id : $uso_cfdi_id); ?>
                            <?php endif; ?>

                            <?php if ($isInvoicePayment) : ?>
                              <?= $gCatalogsHelper->getCFDICatalog(23, true); ?>
                            <?php endif; ?>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-enviar_al_correo">Enviar al correo<span class="text-danger">*</span></label>

                          <select id="fdf-enviar_al_correo" class="form-control form-select" name="enviar_al_correo" required>
                            <option value="">--Seleccionar--</option>
                            <option value="si" selected>Si</option>
                            <option value="no">No</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-correo">Correo</label>
                          <input id="fdf-correo" class="form-control" name="correo" value="<?= $customerEmail; ?>" type="text">
                        </div>
                      </div>
                    </div>
                    <!-- END FACTURAR A -->

                    <!-- START DETALLES DE PAGO -->
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-dollar-sign me-1"></i> Detalles de pago</h5>

                    <?php if ($isInvoicePayment) : ?>
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-group">
                            <label class="form-label" for="fd-cfdi_relacionado">CFDI Relacionado<span class="text-danger">*</span> <span class="fw-light">CFDI (Formato: 17CD1DDC-FF52-4D68-A3C8-AC336FBF7FD0)</span></label>
                            <select id="fd-cfdi_relacionado" class="form-control form-select select2" name="cfdi_relacionado" required>
                              <option value="">--Seleccionar--</option>

                              <?php if ($invoiceFromPayment) : ?>
                                <option value="<?= $invoiceFromPayment["uuid"]; ?>" selected><?= $invoiceFromPayment["serie"] ?>-<?= $invoiceFromPayment["folio"]; ?> | <?= $invoiceFromPayment["uuid"]; ?></option>
                              <?php endif; ?>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>

                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="fdf-metodo_pago">Método de pago<span class="text-danger">*</span></label>

                          <select id="fdf-metodo_pago" class="form-control form-select" name="metodo_pago" required>
                            <option value="">--Seleccionar--</option>
                            <option value="PUE" selected>PUE - Pago de una sola exibición</option>

                            <?php if ($isInvoiceIncome && $invoiceType == "ingreso") : ?>
                              <option value="PPD">PPD - Pago en parciales o diferido</option>
                            <?php endif; ?>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_forma_pago">Forma de pago<span class="text-danger">*</span></label>

                          <select id="fdf-id_forma_pago" class="form-control form-select" name="id_forma_pago" required>
                            <option value="">--Seleccionar--</option>

                            <?= $gCatalogsHelper->getPaymentMethods($paymentFormId); ?>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="fdf-moneda">Moneda<span class="text-danger">*</span></label>

                          <input id="fdf-moneda" class="form-control" name="moneda" value="MXN" type="text" readonly required>
                        </div>
                      </div>
                    </div>

                    <?php if ($isInvoicePayment) : ?>
                      <div class="row">
                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fdf-fecha_pago">Fecha de pago<span class="text-danger">*</span></label>

                            <input id="fdf-fecha_pago" class="form-control datepicker" name="fecha_pago" value="<?= date("d-m-Y"); ?>" type="text" required>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-monto">Monto<span class="text-danger">*</span></label>
                            <input id="fd-monto" class="form-control input-number" name="monto" value="<?= $payment_monto; ?>" type="text" required>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-num_parcialidad">Num. parcialidad<span class="text-danger">*</span></label>
                            <input id="fd-num_parcialidad" class="form-control input-number" name="num_parcialidad" type="number" value="<?= $payment_num_parcialidad; ?>" required>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-importe_saldo_anterior">Importe Saldo anterior<span class="text-danger">*</span></label>
                            <input id="fd-importe_saldo_anterior" class="form-control input-number" name="importe_saldo_anterior" value="<?= $payment_importe_saldo_anterior; ?>" type="number" required>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-importe_pagado">Importe pagado<span class="text-danger">*</span></label>
                            <input id="fd-importe_pagado" class="form-control input-number" name="importe_pagado" value="<?= $payment_monto; ?>" type="number" required>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-importe_saldo_insoluto">Importe Saldo insuluto<span class="text-danger">*</span></label>
                            <input id="fd-importe_saldo_insoluto" class="form-control input-number" name="importe_saldo_insoluto" value="<?= $payment_importe_saldo_insoluto; ?>" type="number" required>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-6 col-md-3 col-lg-2">
                          <div class="form-group">
                            <label class="form-label" for="fd-objeto_impuesto_dr">Objedo de impuesto DR<span class="text-danger">*</span></label>
                            <select id="fd-objeto_impuesto_dr" class="form-control form-select" name="objeto_impuesto_dr" required>
                              <option value="">--Seleccionar</option>
                              <option value="01">No objeto de impuesto.</option>
                              <option value="02" selected="">Sí objeto de impuesto.</option>
                              <option value="03">Sí objeto de impuesto y no obligado al desglose.</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2 impuesto-dr-container">
                          <div class="form-group">
                            <label class="form-label" for="fd-impuesto_dr">Impuesto DR<span class="text-danger">*</span></label>
                            <select id="fd-impuesto_dr" class="form-control form-select" name="impuesto_dr" required readonly style="pointer-events: none;">
                              <option value="">--Seleccionar</option>
                              <option value="001">ISR.</option>
                              <option value="002" selected="">IVA.</option>
                              <option value="003">IEPS.</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-6 col-md-3 col-lg-2 impuesto-dr-container">
                          <div class="form-group">
                            <label class="form-label" for="fd-tipo_factor_dr">Tipo Factor DR<span class="text-danger">*</span></label>
                            <select id="fd-tipo_factor_dr" class="form-control form-select" name="tipo_factor_dr" required readonly style="pointer-events: none;">
                              <option value="">--Seleccionar</option>
                              <option value="Tasa" selected>Tasa</option>
                              <option value="Cuota">Cuota</option>
                              <option value="Exento" disabled>Exento</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                    <!-- END DETALLES DE PAGO -->

                    <!-- START CFDI RELACIONADO -->
                    <?php if ($isInvoiceIncome or $isInvoiceDischarge) : ?>
                      <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-box me-1"></i>CFDI Relacionado</h5>

                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-group">
                            <label class="form-label" for="fd-tipo_relacion">Tipo de relación</label>

                            <select id="fd-tipo_relacion" class="form-control form-select" name="tipo_relacion">
                              <option value="">--Seleccionar--</option>
                              <option value="01">Nota de crédito de los documentos relacionados</option>
                              <option value="02">Nota de débito de los documentos relacionados</option>
                              <option value="03">Devolución de mercancía sobre facturas o traslados previos</option>
                              <option value="04">Sustitución de los CFDI previos</option>
                              <option value="05">Traslados de mercancias facturados previamente</option>
                              <option value="06">Factura generada por los traslados previos</option>
                              <option value="07">CFDI por aplicación de anticipo</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-6">
                          <div class="form-group">
                            <label class="form-label" for="fd-cfdi_relacionado">CFDI Relacionados<span id="fd-cfdi_relacionado-required" class="text-danger"></span> <span class="fw-light">CFDI (Formato: 17CD1DDC-FF52-4D68-A3C8-AC336FBF7FD0)</span></label>
                            <select id="fd-cfdi_relacionado" class="form-control form-select select2" name="cfdi_relacionado[]" multiple>
                              <option value="">--Seleccionar--</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                    <!-- END CFDI RELACIONADO -->

                    <!-- START COMENTARIOS ADICIONALES -->
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-comment me-1"></i>Comentarios adicionales</h5>

                    <div class="row">
                      <div class="col-12">
                        <div class="form-group">
                          <label class="form-label" for="fd-comentarios">Comentarios</label>
                          <textarea id="fd-comentarios" class="form-control" name="comentarios" rows="3"></textarea>
                        </div>
                      </div>
                    </div>
                    <!-- END COMENTARIOS ADICIONALES -->

                    <!-- START PRODUCTOS -->
                    <?php if ($isInvoiceIncome or $isInvoiceDischarge) : ?>
                      <?php if (!$saleUids) : ?>
                        <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-box me-1"></i>Productos</h5>

                        <div class="row">
                          <div class="col-12 col-md-3 col-lg-1">
                            <div class="form-group">
                              <label class="form-label" for="fdf-cantidad">Cantidad<span class="text-danger">*</span></label>
                              <input id="fdf-cantidad" class="form-control" name="cantidad" min="1" value="1" type="number">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-5">
                            <div class="form-group">
                              <label class="form-label" for="fdf-id_producto">Producto<span class="text-danger">*</span></label>
                              <select id="fdf-id_producto" class="form-control form-select select2" name="id_producto">
                                <option value="">--Seleccionar--</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-3">
                            <div class="form-group">
                              <label class="form-label" for="fdf-id_clave_unidad">Unidad de medida<span class="text-danger">*</span> <i class="fa fa-exclamation-circle text-info h4 m-0 cursor-pointer" data-bs-toggle="tooltip" data-bs-title="Escribe las tres primeras letras de la unidad de medida, o la clave completa conforme al catálogo del SAT."></i></label>
                              <select id="fdf-id_clave_unidad" class="form-control form-select select2" name="id_clave_unidad">
                                <option value="">--Seleccionar--</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-3">
                            <div class="form-group">
                              <label class="form-label" for="fdf-id_clave_producto_servicio">Clave producto/servicio<span class="text-danger">*</span> <i class="fa fa-exclamation-circle text-info h4 m-0 cursor-pointer" data-bs-toggle="tooltip" data-bs-title="Escribe las tres primeras letras de la descripción, o la clave completa conforme al catálogo del SAT."></i></label>
                              <select id="fdf-id_clave_producto_servicio" class="form-control form-select select2" name="id_clave_producto_servicio">
                                <option value="">--Seleccionar--</option>
                              </select>
                            </div>
                          </div>
                        </div>

                        <div class="row g-2 align-items-end">
                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-precio_unitario">Precio unitario<span class="text-danger">*</span> <i class="fa fa-exclamation-circle text-info h4 m-0 cursor-pointer" data-bs-toggle="tooltip" data-bs-title="Captura el valor o precio unitario del producto o servicio."></i></label>
                              <input id="fdf-precio_unitario" class="form-control" name="precio_unitario" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-importe">Importe<span class="text-danger">*</span> <i class="fa fa-exclamation-circle text-info h4 m-0 cursor-pointer" data-bs-toggle="tooltip" data-bs-title="Es el importe total de los productos o servicios a facturar; el monto se obtiene de multiplicar la cantidad por el valor unitario."></i></label>
                              <input id="fdf-importe" class="form-control" name="importe" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" readonly>
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-descuento">Descuento<span class="text-danger">*</span> <i class="fa fa-exclamation-circle text-info h4 m-0 cursor-pointer" data-bs-toggle="tooltip" data-bs-title="Captura el importe de los descuentos aplicables a los productos o servicios que vas a facturar."></i></label>
                              <input id="fdf-descuento" class="form-control" name="descuento" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number">
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-objeto_impuesto">Obj. de impuesto</label>

                              <select id="fdf-objeto_impuesto" class="form-control form-select" name="objeto_impuesto">
                                <option value="">--Seleccionar--</option>
                                <option value="01">No objeto de impuesto.</option>
                                <option value="02" selected>Sí objeto de impuesto.</option>
                                <option value="03">Sí objeto del impuesto y no obligado al desglose.</option>
                              </select>
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-iva">IVA<span class="text-danger">*</span></label>
                              <input id="fdf-iva" class="form-control" name="iva" value="16" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" readonly>
                            </div>
                          </div>

                          <div class="col-12 col-md-4 col-lg-2">
                            <div class="form-group">
                              <label class="form-label" for="fdf-ieps">IEPS<span class="text-danger">*</span></label>
                              <input id="fdf-ieps" class="form-control" name="ieps" value="0" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" readonly>
                            </div>
                          </div>
                        </div>

                        <div class="row">
                          <div class="col-12 col-md-4">
                            <div class="form-group">
                              <label class="form-label" for="fdf-comments">Comentarios</label>
                              <textarea id="fdf-comments" class="form-control" name="comments"></textarea>
                            </div>
                          </div>
                        </div>

                        <div class="row mb-3">
                          <div class="col-12 text-center">
                            <button id="btn-add-product" class="btn btn-secondary" type="button">
                              <i class="fa fa-plus me-1"></i> Agregar
                            </button>
                          </div>
                        </div>
                      <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($isInvoicePayment) : ?>
                      <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-box me-1"></i>Conceptos</h5>
                    <?php endif; ?>

                    <div class="row">
                      <div id="invoice-table" class="col-12"></div>
                    </div>
                    <!-- END PRODUCTOS -->
                  <?php endif; ?>
                </div>

                <?php if ($canInvoice) : ?>
                  <input id="product-rows" name="productos" type="hidden">

                  <input name="uid" type="hidden">
                  <input name="action" value="create-invoice" type="hidden">
                  <input name="place" value="<?= str_replace("/", "-", $page_config['page_identifier']); ?>" type="hidden">

                  <div class="card-footer text-end">
                    <div class="btn-group">
                      <button id="btn-view-invoice" class="btn btn-secondary" type="button" style="<?= ($sale || $saleUids) ? "" : "display: none;" ?>">
                        <i class="fa fa-eye me-1"></i>
                        Visualizar factura
                      </button>

                      <button class="btn btn-primary" type="submit">
                        <i class="fa fa-plus-circle me-1"></i>
                        Generar factura
                      </button>
                    </div>
                  </div>
                <?php endif; ?>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- MODALS -->
      <?php include 'src/modals/' . $page_config['page_identifier'] . '.php'; ?>

      <?php
      $clientes_modal_page_id = "clientes";
      $clientes_modal_origin  = "facturas-nueva";
      include __DIR__ . "/src/modals/clientes.php";
      ?>

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

  <!-- VALIDATE JS -->
  <script src="<?= BASE_URL; ?>/src/js/validate.init.js"></script>

  <!-- SELECT2 -->
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  <script src="<?= BASE_URL; ?>/src/js/select2.autocomplete.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/invoice-tablev3.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/invoices-payments.js"></script>

  <script>
    /**
     * START Información básica
     */
    $("#fdf-tipo_factura-label").on("change", function() {
      var tipoFactura = $(this).val();
      updateUrlParams("tipo_factura", tipoFactura);
      location.reload();
    });
    /**
     * END Información básica
     */

    /**
     * START Facturar a
     */
    const setCustomerFields = data => {
      $("#fdf-razon_social").val(data.businessName);
      $("#fdf-cliente_rfc").val(data.rfc);
      $("#fdf-id_regimen_fiscal").val(data.taxRegimeId).trigger('change');
      $("#fdf-cliente_domicilio_fiscal").val(data.taxResidence);
      $("#fdf-correo").val(data.email);

      if (data.email) $("#fdf-enviar_al_correo").val("si").trigger('change');
      if (!data.email) $("#fdf-enviar_al_correo").val("no").trigger('change');

      let cp = null;

      <?php if ($IS_ADMIN) : ?>
        cp = $("#fdf-id_sucursal option:selected").data("cp");
        setTimeout(() => {
          if (data.rfc === "XAXX010101000") $("#fdf-cliente_domicilio_fiscal").val(cp);
        }, 200);
      <?php endif; ?>

      <?php if (!$IS_ADMIN) : ?>
        cp = `<?= $userBranchOfficeData["cp"]; ?>`;
        setTimeout(() => {
          if (data.rfc === "XAXX010101000") $("#fdf-cliente_domicilio_fiscal").val(cp);
        }, 200);
      <?php endif; ?>
    };

    const autocompleteCustomers = new Select2Autocomplete({
      selector: "#fdf-id_cliente",
      url: "<?= BASE_URL; ?>/data/autocompletes/clientes_data.php",
      onSelect: data => setCustomerFields(data)
    });

    <?php if ($IS_ADMIN) : ?>
      $("#fdf-id_sucursal").on("change", function() {
        const cp = $("#fdf-id_sucursal option:selected").data("cp");
        const customerRFC = $("#fdf-cliente_rfc").val();

        if (customerRFC === "XAXX010101000") $("#fdf-cliente_domicilio_fiscal").val(cp);
      });
    <?php endif; ?>
    /**
     * END Facturar a
     */

    /**
     * START Detalles de pago
     */
    <?php if ($isInvoicePayment) : ?>
      let fidURL = "<?= BASE_URL; ?>/data/autocompletes/facturas-ingreso_data.php";

      const autocompleteInvoices = new Select2Autocomplete({
        selector: "#fd-cfdi_relacionado",
        url: fidURL,
        onSelect: data => {
          $("#fd-monto").val("0");
          $("#fd-num_parcialidad").val(data.num_parcialidad);
          $("#fd-importe_saldo_anterior").val(data.importe_saldo_anterior);
          $("#fd-importe_pagado").val(data.importe_pagado);
        }
      });
    <?php endif; ?>

    <?php /* if ($invoiceFromPayment) : ?>
      //fidURL += "?value=<?= $invoiceFromPayment["uuid"]; ?>";
      autocompleteInvoices._setValue("<?= $invoiceFromPayment["uuid"]; ?>");
      //$("#fd-cfdi_relacionado").trigger("change");

      $(function() {
        $("#fd-cfdi_relacionado").click();
        setTimeout(() => {
          $("#fd-cfdi_relacionado").val("<?= $invoiceFromPayment["uuid"]; ?>").trigger("change");
        }, 1000);
      });
    <?php endif; */ ?>

    $("#fdf-metodo_pago").on("change", function() {
      var metodoPago = $(this).val();

      if (metodoPago == "PUE") $("#fdf-id_forma_pago").val("").removeAttr("readonly").removeAttr("style");
      if (metodoPago == "PPD") $("#fdf-id_forma_pago").val("22").attr("readonly", true).attr("style", "pointer-events: none;");
    });

    const invoicesPayments = new InvoicesPayments({
      selector: "facturas-pagos"
    });

    const calcSaldoInsuluto = () => {
      const saldoAnterior = parseFloat($("#fd-importe_saldo_anterior").val());
      const importePagado = parseFloat($("#fd-importe_pagado").val());

      const saldoInsoluto = (saldoAnterior - importePagado).toFixed(DECIMALS_CURRENCY);

      $("#fd-importe_saldo_insoluto").val(saldoInsoluto);
    };

    $("#fd-importe_saldo_anterior").on("keyup", calcSaldoInsuluto);
    $("#fd-importe_pagado").on("keyup", function() {
      $("#fd-monto").val($(this).val());
      calcSaldoInsuluto();
    });
    $("#fd-monto").on("keyup", function() {
      const monto = $(this).val();

      $("#fd-importe_pagado").val(monto).trigger('keyup');
    });

    invoicesPayments._init();


    /**
     * END Detalles de pago
     */

    /**
     * START CFDI RELACIONADO
     */
    <?php if ($isInvoiceIncome or $isInvoiceDischarge) : ?>
      let autocompleteInvoicesIncome = new Select2Autocomplete({
        selector: "#fd-cfdi_relacionado",
        url: "<?= BASE_URL; ?>/data/autocompletes/facturas-all_data.php"
      });

      $("#fd-tipo_relacion").on("change", function() {
        const value = $(this).val();

        if (value) {
          $("#fd-cfdi_relacionado").prop("required", true);
          $("#fd-cfdi_relacionado-required").html("*");

          // if (value == "07") autocompleteInvoicesIncome = new Select2Autocomplete({
          //   selector: "#fd-cfdi_relacionado",
          //   url: "<?= BASE_URL; ?>/data/autocompletes/facturas-anticipo-compra_data.php"
          // });

          // if (value != "07") autocompleteInvoicesIncome = new Select2Autocomplete({
          //   selector: "#fd-cfdi_relacionado",
          //   url: "<?= BASE_URL; ?>/data/autocompletes/facturas-ingreso-all_data.php"
          // });
        } else {
          $("#fd-cfdi_relacionado").prop("required", false);
          $("#fd-cfdi_relacionado-required").html("");
        }
      });
    <?php endif; ?>
    /**
     * END CFDI RELACIONADO
     */

    /**
     * START Productos
     */
    let invoiceRows = [];

    let invoiceTableProps = {
      id: "invoice",
      onRender: rows => {
        console.log(rows);
        $("#product-rows").val(JSON.stringify(rows))
      }
    };

    <?php if ($sale) : ?>
      invoiceTableProps.rounded = <?= $sale->rounding ?? 0; ?>;
    <?php endif; ?>

    const invoiceTable = new InvoiceTable(invoiceTableProps);

    const autocompleteUnitsMeasurement = new Select2Autocomplete({
      selector: "#fdf-id_clave_unidad",
      url: "<?= BASE_URL; ?>/data/autocompletes/unidad-de-medidas_data.php"
    });

    const autocompleteKeyProductService = new Select2Autocomplete({
      selector: "#fdf-id_clave_producto_servicio",
      url: "<?= BASE_URL; ?>/data/autocompletes/clave-producto-servicios_data.php"
    });

    const calculateImport = () => {
      const quantity = $("#fdf-cantidad").val();
      const unitPrice = $("#fdf-precio_unitario").val();

      $("#fdf-importe").val((parseFloat(unitPrice) * quantity).toFixed(DECIMALS_CURRENCY));
    };

    const autocompleteProducts = new Select2Autocomplete({
      selector: "#fdf-id_producto",
      url: "<?= BASE_URL; ?>/data/autocompletes/productos_data.php",
      onSelect: data => {
        const applyIva = data.applyIva;
        const applyIeps = data.applyIeps;
        const iepsPercentage = parseFloat(data.iepsPercentage ?? 0) || 0;

        autocompleteUnitsMeasurement._setValue(data.unitId, data.unitName);
        autocompleteKeyProductService._setValue(data.productServiceId, data.productServiceDescription);

        $("#fdf-precio_unitario").val(data.originalSalePrice);
        $("#fdf-descuento").val(0);
        $("#fdf-id_producto").data("have-iva", applyIva == "si" ? 1 : 0);
        $("#fdf-id_producto").data("have-ieps", applyIeps == "si" ? 1 : 0);
        $("#fdf-id_producto").data("ieps-percentage", iepsPercentage);

        const shouldHaveTaxes = applyIva == "si" || applyIeps == "si";
        $("#fdf-objeto_impuesto").val(shouldHaveTaxes ? "02" : "01").trigger('change');

        calculateImport();
      }
    });

    const syncTaxRateFields = () => {
      const taxObject = $("#fdf-objeto_impuesto").val();
      const haveIVAByProduct = parseInt($("#fdf-id_producto").data("have-iva") ?? 0) === 1;
      const haveIEPSByProduct = parseInt($("#fdf-id_producto").data("have-ieps") ?? 0) === 1;
      const iepsPercentageByProduct = parseFloat($("#fdf-id_producto").data("ieps-percentage") ?? 0) || 0;

      if (taxObject == "02") {
        $("#fdf-iva").val(haveIVAByProduct ? "16" : "0");
        $("#fdf-ieps").val(haveIEPSByProduct ? iepsPercentageByProduct : "0");
      } else {
        $("#fdf-iva").val("0");
        $("#fdf-ieps").val("0");
      }
    };

    const addProduct = () => {
      const productId = $("#fdf-id_producto").val();
      const productName = $("#fdf-id_producto option:selected").text();

      const unitMeasurementId = $("#fdf-id_clave_unidad").val();
      const unitMeasurementName = $("#fdf-id_clave_unidad option:selected").text();

      const keyProductServiceId = $("#fdf-id_clave_producto_servicio").val();
      const keyProductServiceName = $("#fdf-id_clave_producto_servicio option:selected").text();

      const quantity = parseInt($("#fdf-cantidad").val());
      const unitPrice = parseFloat($("#fdf-precio_unitario").val());
      const amount = parseFloat($("#fdf-importe").val());
      const discount = parseFloat($("#fdf-descuento").val());
      //const discount = 0;

      const taxObject = $("#fdf-objeto_impuesto").val();
      const haveIVA = parseFloat($("#fdf-iva").val()) > 0 ? true : false;
      const haveIEPS = parseFloat($("#fdf-ieps").val()) > 0 ? true : false;
      const iepsPercentage = parseFloat($("#fdf-ieps").val()) || 0;
      const taxableBase = (unitPrice * quantity) - discount;
      const iepsCurrency = haveIEPS ? (taxableBase * (iepsPercentage / 100)) : 0;

      const comments = $("#fdf-comments").val();

      if (productId === "" || unitMeasurementId === "" || keyProductServiceId === "" || quantity === 0 || unitPrice === 0 || amount === 0) {
        showSweetToast({
          icon: "error",
          message: "Todos los campos son requeridos."
        });
        return;
      }

      const row = {
        productId,
        productName,
        unitMeasurementId,
        unitMeasurementName,
        keyProductServiceId,
        keyProductServiceName,
        quantity,
        unitPrice,
        amount,
        discount,
        taxObject,
        haveIVA,
        haveIEPS,
        iepsPercentage,
        iepsCurrency,
        comments
      };

      $("#btn-view-invoice").show();

      invoiceTable._addRow(row);
      invoiceTable._render();
    };

    $("#fdf-objeto_impuesto").on("change", function() {
      syncTaxRateFields();
    });

    $("#fdf-id_producto").on("change", function() {
      if (!$(this).val()) {
        $(this).removeData("have-iva");
        $(this).removeData("have-ieps");
        $(this).removeData("ieps-percentage");
      }

      syncTaxRateFields();
    });

    $("#btn-add-product").on("click", () => addProduct())
    $("#fdf-cantidad").on("keyup", () => calculateImport());
    $("#fdf-precio_unitario").on("keyup", () => calculateImport());

    <?php if ($isInvoicePayment) : ?>
      const conceptRow = {
        productId: "84111506",
        productName: "Pago",
        unitMeasurementId: "ACT",
        unitMeasurementName: "ACT",
        keyProductServiceId: "84111506",
        keyProductServiceName: "84111506",
        quantity: 1,
        unitPrice: 0,
        amount: 0,
        discount: 0,
        taxObject: "01",
        iva: 0,
        haveIEPS: false,
        iepsPercentage: 0,
        iepsCurrency: 0
      };

      invoiceTable._addRow(conceptRow);
      invoiceTable._render();
    <?php endif; ?>
    /**
     * END Productos
     */

    <?php if ($sale || $isInvoiceIncome) : ?>
      const list = <?= json_encode($products); ?>;

      const isInvoiceDischarge = <?= $isInvoiceDischarge ? 'true' : 'false'; ?>;
      const multiplicador = isInvoiceDischarge ? -1 : 1;

      list && list.map(item => {
        const row = {
          saleFolio: item?.saleFolio,
          productId: item.productId,
          productName: item.productName,
          unitMeasurementId: item.unitMeasurementId,
          unitMeasurementName: item.unitMeasurementName,
          keyProductServiceId: item.keyProductServiceId,
          keyProductServiceName: item.keyProductServiceName,
          quantity: item.quantity,
          unitPrice: item.unitPrice * multiplicador,
          amount: item.amount * multiplicador,
          discount: item.discount * multiplicador,
          taxObject: item.taxObject,
          haveIVA: item.haveIVA,
          haveIEPS: item?.haveIEPS,
          iepsPercentage: item?.iepsPercentage ? parseFloat(item?.iepsPercentage) : 0,
          iepsCurrency: item?.iepsCurrency ? parseFloat(item?.iepsCurrency) * multiplicador : null,
          serialNumbers: item.serialNumbers,
          ivaCurrency: item?.ivaCurrency ? parseFloat(item?.ivaCurrency) : null,
          comments: item?.comments ? item?.comments : null
        };

        invoiceTable._addRow(row);
      });

      invoiceTable._render();
      autocompleteCustomers._setValue("<?= $sale->customer->id; ?>", "<?= $sale->customer->name; ?>");
    <?php endif; ?>

    <?php if ((!$sale && $defaultCustomer) || $salePaymentData) : ?>
      autocompleteCustomers._setValue("<?= $customerFormId; ?>", "<?= addslashes($customerFormName); ?>");
      setCustomerFields({
        businessName: "<?= addslashes($customerBusinessName); ?>",
        rfc: "<?= addslashes($customerRFC); ?>",
        taxRegimeId: "<?= $customerTaxRegimeId; ?>",
        taxResidence: "<?= addslashes($customerTaxResidence); ?>",
        email: "<?= addslashes($customerEmail); ?>"
      });
    <?php endif; ?>

    $("#btn-add-folio").on("click", function() {
      const folio = $("#fdf-folioVenta").val();

      if (folio === "") {
        showSweetToast({
          icon: "error",
          message: "El folio es requerido."
        });
        return;
      }

      updateUrlParams("folio", folio);
      location.reload();
    });

    $("#fdf-folioVenta").on("keydown", function(e) {
      if (e.keyCode === 13) {
        e.preventDefault();

        const folio = $(this).val();

        if (folio === "") {
          showSweetToast({
            icon: "error",
            message: "El folio es requerido."
          });
          return;
        }

        updateUrlParams("folio", folio);
        location.reload();
      }
    });

    $("#btn-view-invoice").on("click", () => {
      //$("#invoice-form").attr("target", "_blank").submit();

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = "<?= BASE_URL;  ?>/facturas/visualizar";
      form.target = 'invoice-preview';

      // Obtener todos los campos del formulario original en un objeto
      const formData = new FormData(document.getElementById('invoice-form'));

      // Agregar los campos al nuevo formulario
      formData.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
      });

      document.body.appendChild(form);
      form.submit();
      document.body.removeChild(form);
    });

    $("#fd-cfdi_relacionado").on("change", function() {
      $("#btn-view-invoice").show();
    });
  </script>
</body>

</html>