<?php
include "inc/session.inc.php";

require_once __DIR__ . "/data/lib/helpers/emisores.helpers.php";
require_once __DIR__ . "/data/lib/helpers/g-catalogs.helpers.php";

$page_config = [
  'page_identifier'   => 'facturas/nueva',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
];

$pcModule = checkModuleActionPermission($page_config['page_identifier'], 'ver', true);
$page_config['page_title'] = $pcModule->name;

$invoiceType      = cleanStr($_GET['tipo_factura']);
$canInvoice       = $invoiceType ? true : false;
$isInvoiceIncome  = $invoiceType == 'I' ? true : false;
$isInvoicePayment = $invoiceType == 'P' ? true : false;

$emisoresHelper   = new EmisoresHelper();
$gCatalogsHelper  = new GCatalogsHelpers();
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
          <div class="row">
            <div class="col-12">
              <form id="invoice-form" class="form-validate card">
                <div class="card-body">
                  <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fa fa-list me-1"></i> Información básica</h5>

                  <div class="row">
                    <div class="col-12 col-md-6 col-lg-4">
                      <div class="form-group">
                        <label class="form-label" for="fdf-tipo_factura">Tipo de factura<span class="text-danger">*</span></label>

                        <select id="fdf-tipo_factura" class="form-control form-select" name="tipo_factura" required>
                          <option value="">--Seleccionar--</option>
                          <option value="I" <?= $isInvoiceIncome ? "selected" : ""; ?>>I Ingreso</option>
                          <option value="P" <?= $isInvoicePayment ? "selected" : ""; ?>>P Pago</option>
                        </select>
                      </div>
                    </div>

                    <?php if ($canInvoice) : ?>
                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_emisor">Emisor<span class="text-danger">*</span></label>

                          <select id="fdf-id_emisor" class="form-control form-select" name="id_emisor" required>
                            <option value="">--Seleccionar--</option>
                            <?= $emisoresHelper->getCatalog(); ?>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-4">
                        <div class="form-group">
                          <label class="form-label" for="fdf-fecha_emision">Fecha de emisión<span class="text-danger">*</span></label>

                          <input id="fdf-fecha_emision" class="form-control datepicker" name="fecha_emision" value="<?= date("d-m-Y"); ?>" type="text" required>
                        </div>
                      </div>
                    <?php endif; ?>
                  </div>

                  <?php if ($canInvoice) : ?>
                    <!-- START FACTURAR A -->
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="far fa-user me-1"></i> Facturar a</h5>

                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_cliente">Cliente<span class="text-danger">*</span></label>
                          <select id="fdf-id_cliente" class="form-control form-select select2" name="id_cliente" required>
                            <option value="">--Seleccionar--</option>
                          </select>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-razon_social">Razón social<span class="text-danger">*</span></label>
                          <input id="fdf-razon_social" class="form-control" name="razon_social" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-cliente_rfc">RFC<span class="text-danger">*</span></label>
                          <input id="fdf-cliente_rfc" class="form-control" name="cliente_rfc" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_regimen_fiscal">Régimen fiscal<span class="text-danger">*</span></label>

                          <select id="fdf-id_regimen_fiscal" class="form-control form-select" name="id_regimen_fiscal" required>
                            <option value="">--Seleccionar--</option>
                            <?= $gCatalogsHelper->getTaxRegime(); ?>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-cliente_domicilio_fiscal">Domicilio fiscal<span class="text-danger">*</span></label>
                          <input id="fdf-cliente_domicilio_fiscal" class="form-control" name="cliente_domicilio_fiscal" type="text" required>
                        </div>
                      </div>

                      <div class="col-12 col-md-6 col-lg-3">
                        <div class="form-group">
                          <label class="form-label" for="fdf-id_uso_cfdi">Uso de CFDI<span class="text-danger">*</span></label>

                          <select id="fdf-id_uso_cfdi" class="form-control form-select" name="id_uso_cfdi" required>
                            <option value="">--Seleccionar--</option>
                            <?= $gCatalogsHelper->getCFDICatalog(); ?>
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
                          <input id="fdf-correo" class="form-control" name="correo" type="text">
                        </div>
                      </div>
                    </div>
                    <!-- END FACTURAR A -->

                    <!-- START DETALLES DE PAGO -->
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-dollar-sign me-1"></i> Detalles de pago</h5>

                    <?php if ($isInvoiceIncome) : ?>
                      <div class="row">
                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label class="form-label" for="fdf-metodo_pago">Método de pago<span class="text-danger">*</span></label>

                            <select id="fdf-metodo_pago" class="form-control form-select" name="metodo_pago" required>
                              <option value="">--Seleccionar--</option>
                              <option value="PUE">Pago de una sola exibición</option>
                              <option value="PPD">Pago en parciales o diferido</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4">
                          <div class="form-group">
                            <label class="form-label" for="fdf-id_forma_pago">Forma de pago<span class="text-danger">*</span></label>

                            <select id="fdf-id_forma_pago" class="form-control form-select" name="id_forma_pago" required>
                              <option value="">--Seleccionar--</option>

                              <?= $gCatalogsHelper->getPaymentMethods(); ?>
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
                    <?php endif; ?>

                    <?php if ($isInvoicePayment) : ?>
                      <div class="row">
                        <div id="facturas-pagos-container" class="col-12"></div>
                      </div>

                      <div class="row">
                        <div class="col-12 text-center">
                          <button id="facturas-pagos-btn-add-row" class="btn btn-secondary" type="button">
                            <i class="fa fa-plus me-1"></i> Agregar factura
                          </button>
                        </div>
                      </div>

                      <!-- <div class="card">
                        <div class="card-body">
                          <h3 class="header-title mb-3">Facturas</h3>

                          <div class="row">
                            <div class="col-12 col-md-6 col-lg-6">
                              <div class="form-group">
                                <label class="form-label" for="fd-cfdi_relacionado">CFDI Relacionado<span class="text-danger">*</span> <span class="fw-light">CFDI (Formato: 17CD1DDC-FF52-4D68-A3C8-AC336FBF7FD0)</span></label>
                                <select id="fdf-cfdi_relacionado" class="form-control form-select select2" name="cfdi_relacionado" required>
                                  <option value="">--Seleccionar--</option>
                                </select>
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-12">
                              <div class="card">
                                <div class="card-body">
                                  <div class="row tipo-factura-pago">
                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fdf-fecha_pago">Fecha de pago<span class="text-danger">*</span></label>

                                        <input id="fdf-fecha_pago" class="form-control datepicker" name="fecha_pago" value="<?= date("d-m-Y"); ?>" type="text" required>
                                      </div>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fd-monto">Monto<span class="text-danger">*</span></label>
                                        <input id="fd-monto" class="form-control input-number" name="monto" step="0.1" type="number" required>
                                      </div>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fd-num_parcialidad">Num. parcialidad<span class="text-danger">*</span></label>
                                        <input id="fd-num_parcialidad" class="form-control input-number" name="num_parcialidad" type="number" required>
                                      </div>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fd-importe_saldo_anterior">Importe Saldo anterior<span class="text-danger">*</span></label>
                                        <input id="fd-importe_saldo_anterior" class="form-control input-number" name="importe_saldo_anterior" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" required>
                                      </div>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fd-importe_pagado">Importe pagado<span class="text-danger">*</span></label>
                                        <input id="fd-importe_pagado" class="form-control input-number" name="importe_pagado" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" required>
                                      </div>
                                    </div>

                                    <div class="col-6 col-md-3 col-lg-2">
                                      <div class="form-group">
                                        <label class="form-label" for="fd-importe_saldo_insoluto">Importe Saldo insuluto<span class="text-danger">*</span></label>
                                        <input id="fd-importe_saldo_insoluto" class="form-control input-number" name="importe_saldo_insoluto" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" required>
                                      </div>
                                    </div>
                                  </div>

                                  <div class="row tipo-factura-pago">
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
                                        <select id="fd-impuesto_dr" class="form-control form-select" name="impuesto_dr" required>
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
                                        <select id="fd-tipo_factor_dr" class="form-control form-select" name="tipo_factor_dr" required>
                                          <option value="">--Seleccionar</option>
                                          <option value="Tasa" selected>Tasa</option>
                                          <option value="Cuota">Cuota</option>
                                          <option value="Exento" disabled>Exento</option>
                                        </select>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div> -->
                    <?php endif; ?>
                    <!-- END DETALLES DE PAGO -->

                    <!-- START PRODUCTOS -->
                    <?php if ($isInvoiceIncome) : ?>
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
                            <label class="form-label" for="fdf-id_clave_unidad">Unidad de medida<span class="text-danger">*</span></label>
                            <select id="fdf-id_clave_unidad" class="form-control form-select select2" name="id_clave_unidad">
                              <option value="">--Seleccionar--</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-12 col-md-4 col-lg-3">
                          <div class="form-group">
                            <label class="form-label" for="fdf-id_clave_producto_servicio">Clave producto<span class="text-danger">*</span></label>
                            <select id="fdf-id_clave_producto_servicio" class="form-control form-select select2" name="id_clave_producto_servicio">
                              <option value="">--Seleccionar--</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-12 col-md-3 col-lg-3">
                          <div class="form-group">
                            <label class="form-label" for="fdf-precio_unitario">Precio unitario<span class="text-danger">*</span></label>
                            <input id="fdf-precio_unitario" class="form-control" name="precio_unitario" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number">
                          </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3">
                          <div class="form-group">
                            <label class="form-label" for="fdf-importe">Importe<span class="text-danger">*</span></label>
                            <input id="fdf-importe" class="form-control" name="importe" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" readonly>
                          </div>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3">
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

                        <div class="col-12 col-md-3 col-lg-3">
                          <div class="form-group">
                            <label class="form-label" for="fdf-iva">IVA<span class="text-danger">*</span></label>
                            <input id="fdf-iva" class="form-control" name="iva" value="16" min="0" step="<?= DECIMALS_CURRENCY_STEP; ?>" type="number" readonly>
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

                    <?php if ($isInvoicePayment) : ?>
                      <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="fas fa-box me-1"></i>Conceptos</h5>
                    <?php endif; ?>

                    <div class="row">
                      <div id="invoice-table" class="col-12">
                        <div class="table-responsive">
                          <table class="table">
                            <thead class="table-light">
                              <th>Unidad/Clave</th>
                              <th>Concepto</th>
                              <th class="text-center">Cantidad</th>
                              <th class="text-end">Precio</th>
                              <th class="text-end">Importe</th>
                              <th class="text-end">IVA</th>
                            </thead>

                            <tbody>
                              <tr>
                                <td>ACT/84111506</td>
                                <td>Pago</td>
                                <td class="text-center">1</td>
                                <td class="text-end">$0.00</td>
                                <td class="text-end">$0.00</td>
                                <td class="text-end">$0.00</td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

                        <div class="ms-auto" style="max-width: 18rem;">
                          <table class="table table-sm table-bordered text-dark">
                            <tbody>
                              <tr>
                                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Subtotal:</td>
                                <td class="text-end">
                                  $0.00
                                  <input name="subtotal" value="0" type="hidden">
                                </td>
                              </tr>

                              <tr>
                                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Descuento:</td>
                                <td class="text-end">
                                  $0.00
                                  <input name="totalDescuento" value="0" type="hidden">
                                </td>
                              </tr>

                              <tr>
                                <td class="text-end fw-bold bg-primary text-dark text-uppercase">IVA:</td>
                                <td class="text-end">
                                  $0.00
                                  <input name="totalIVA" value="0" type="hidden">
                                </td>
                              </tr>

                              <tr>
                                <td class="text-end fw-bold bg-primary text-dark text-uppercase">Total:</td>
                                <td class="text-end">
                                  $0.00
                                  <input name="total" value="0" type="hidden">
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                      </div>
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
                      <button class="btn btn-secondary" type="button">
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
  <script src="<?= BASE_URL; ?>/src/js/invoice-tablev2.js"></script>
  <script src="<?= BASE_URL; ?>/src/js/invoices-payments.js"></script>

  <script>
    /**
     * START Información básica
     */
    $("#fdf-tipo_factura").on("change", function() {
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
    const autocompleteCustomers = new Select2Autocomplete({
      selector: "#fdf-id_cliente",
      url: "<?= BASE_URL; ?>/data/autocompletes/clientes_data.php",
      onSelect: data => {
        $("#fdf-razon_social").val(data.businessName);
        $("#fdf-cliente_rfc").val(data.rfc);
        $("#fdf-id_regimen_fiscal").val(data.taxRegimeId).trigger('change');
        $("#fdf-cliente_domicilio_fiscal").val(data.taxResidence);
        $("#fdf-correo").val(data.email);

        if (data.email) $("#fdf-enviar_al_correo").val("si").trigger('change');
        if (!data.email) $("#fdf-enviar_al_correo").val("no").trigger('change');
      }
    });
    /**
     * END Facturar a
     */

    /**
     * START Detalles de pago
     */
    $("#fdf-metodo_pago").on("change", function() {
      var metodoPago = $(this).val();

      if (metodoPago == "PUE") $("#fdf-id_forma_pago").val("").removeAttr("readonly").removeAttr("style");
      if (metodoPago == "PPD") $("#fdf-id_forma_pago").val("22").attr("readonly", true).attr("style", "pointer-events: none;");
    });

    const invoicesPayments = new InvoicesPayments({
      selector: "facturas-pagos",
      paymentMethodsCatalog: `<?= $gCatalogsHelper->getPaymentMethods(); ?>`
    });

    invoicesPayments._init();

    /**
     * END Detalles de pago
     */

    /**
     * START Productos
     */
    let invoiceRows = [];

    const invoiceTable = new InvoiceTable({
      id: "invoice",
      onRender: rows => {
        console.log(rows);
        $("#product-rows").val(JSON.stringify(rows))
      }
    });

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

        autocompleteUnitsMeasurement._setValue(data.unitId, data.unitName);
        autocompleteKeyProductService._setValue(data.productServiceId, data.productServiceDescription);

        $("#fdf-precio_unitario").val(data.salePrice);
        $("#fdf-descuento").val(0);

        if (applyIva == "si") $("#fdf-objeto_impuesto").val("02").trigger('change');

        calculateImport();
      }
    });

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
      //const discount = parseFloat($("#fdf-descuento").val());
      const discount = 0;

      const taxObject = $("#fdf-objeto_impuesto").val();
      const haveIVA = parseFloat($("#fdf-iva").val()) > 0 ? true : false;

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
        haveIVA
      };

      invoiceTable._addRow(row);
      invoiceTable._render();
    };

    $("#fdf-objeto_impuesto").on("change", function() {
      const taxObject = $(this).val();

      if (taxObject == "02") $("#fdf-iva").val("16");
      if (taxObject != "02") $("#fdf-iva").val("0");
    });

    $("#btn-add-product").on("click", () => addProduct())
    $("#fdf-cantidad").on("keyup", () => calculateImport());
    $("#fdf-precio_unitario").on("keyup", () => calculateImport());
    /**
     * END Productos
     */
  </script>
</body>

</html>