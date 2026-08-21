<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/customers.helper.php";
require_once __DIR__ . "/data/lib/helpers/sales.helper.php";

/* ventas
	1	id_venta Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_usuario	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	id_sucursal	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	4	id_cliente	int(11)			No	1			Cambiar Cambiar	Eliminar Eliminar	
	5	id_cfdi	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	6	id_direccion	int(11)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	7	id_corte_caja	int(11)			Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	8	folio	varchar(100)	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	9	tipo	enum('incremento', 'decremento')	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	10	observaciones	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	11	folio_cotizacion	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	12	subtotal	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	13	iva	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	14	redondeo	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	15	total	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	16	pago_con	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	17	efectivo	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	18	cheque	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	19	cheque_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	20	transferencia	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	21	transferencia_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	22	tarjeta_credito	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	23	tarjeta_credito_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	24	tarjeta_credito_numero	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	25	tarjeta_debito	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	26	tarjeta_debito_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	27	tarjeta_debito_numero	varchar(100)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	28	cambio	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	29	corte	enum('si', 'no')	utf8mb4_bin		No	no			Cambiar Cambiar	Eliminar Eliminar	
	30	status	enum('activo', 'cancelado')	utf8mb4_bin		No	activo			Cambiar Cambiar	Eliminar Eliminar	
	31	fecha_creacion	datetime			No	current_timestamp()			Cambiar Cambiar	Eliminar Eliminar	
	32	tipo_productos	enum('equipo', 'llantas', 'rines', 'refacciones', ...	utf8mb4_bin		No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	33	forma_pago	enum('contado', 'credito')	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	34	pagado	enum('si', 'no')	utf8mb4_bin		No	si			Cambiar Cambiar	Eliminar Eliminar	
*/

/* venta_pagos
	1	id_venta_pago Primaria	int(11)			No	Ninguna		AUTO_INCREMENT	Cambiar Cambiar	Eliminar Eliminar	
	2	id_venta	int(11)			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	3	efectivo_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	4	cheque_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	5	cheque_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	6	transferencia_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	7	transferencia_referencia	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	8	tarjeta_credito_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	9	tarjeta_credito_numero	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	10	tarjeta_debito_monto	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	11	tarjeta_debito_numero	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
	12	monto_total	decimal(22,2)			No	0.00			Cambiar Cambiar	Eliminar Eliminar	
	13	fecha_hora	datetime			No	Ninguna			Cambiar Cambiar	Eliminar Eliminar	
	14	notas	varchar(250)	utf8mb4_bin		Sí	NULL			Cambiar Cambiar	Eliminar Eliminar	
*/

$page_config = [
  'page_title'        => 'Estado de cuenta',
  'page_identifier'   => 'cuentas-por-cobrar-estado-de-cuenta',
  'modal_title_add'   => '',
  'modal_title_edit'  => ''
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

// Obtener todas las ventas
$sales            = [];
$salesTotalAmount = 0.00;

$query  = "SELECT V.*, S.nombre_sucursal FROM {$db_dti}_ventas AS V INNER JOIN {$db_dti}_sucursales AS S ON V.id_sucursal = S.id_sucursal WHERE V.id_cliente = ? AND V.forma_pago = 'credito' AND V.status = 'activo' ORDER BY V.id_venta DESC";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("i", $customersModel->getId());
$stmt->execute();

$result   = $stmt->get_result();
$numRows  = $result->num_rows;

if ($numRows > 0) {
  while ($row = $result->fetch_assoc()) {
    // Obtener el total a pagar de la venta
    $totalAmount      = getSaleTotalById($row['id_venta']);
    $totalPaid        = getSaleTotalPaidById($row['id_venta']);
    $balance          = round($totalAmount - $totalPaid, DECIMALS_CURRENCY);

    $salesTotalAmount += floatval($totalAmount);

    $row["sale_totalToPay"] = $totalAmount;
    $row["sale_totalPaid"]  = $totalPaid;
    $row["sale_balance"]    = $balance;
    $row["sale_products"]   = get_sale_details_table($row["id_venta"]);
    $sales[] = $row;
  }
}

// Obtener todos los pagos realizados
$payments = [];
$paymentsTotalAmount = 0.00;

$query  = "SELECT VP.*, V.folio AS folio_venta FROM {$db_dti}_venta_pagos AS VP INNER JOIN {$db_dti}_ventas V ON VP.id_venta = V.id_venta WHERE V.id_cliente = ? ORDER BY VP.id_venta_pago DESC";
$stmt   = $mysqli->prepare($query);

$stmt->bind_param("i", $customersModel->getId());
$stmt->execute();

$result   = $stmt->get_result();
$numRows  = $result->num_rows;

if ($numRows > 0) {
  while ($row = $result->fetch_assoc()) {
    $paymentsTotalAmount += floatval($row['monto_total']);
    $payments[] = $row;
  }
}

// Obtener el saldo pendiente
$pendingBalance = $salesTotalAmount - $paymentsTotalAmount;
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
                    <div class="float-start"></div>

                    <div class="float-end">
                      <h4 class="m-0 d-print-none">Estado de cuenta</h4>
                    </div>
                  </div>
                  <!-- end row -->

                  <div class="row mt-3">
                    <div class="col-lg-6">
                      <h5>Información del Cliente</h5>
                      <address>
                        <?= $customersModel->getName(); ?><br>
                        RFC: <?= $customersModel->getRfc(); ?><br>
                        Email: <?= $customersModel->getEmail(); ?><br>
                        <abbr title="Phone">Tel:</abbr> <?= $customersModel->getPhone() ? formatPhoneNumber($customersModel->getPhone()) : "--"; ?>
                      </address>
                    </div> <!-- end col -->

                    <div class="col-lg-6">
                      <h5>Resumen de la cuenta</h5>
                      <address>
                        <span class="fw-bold">Monto total vendido:</span> $<?= number_format($salesTotalAmount, DECIMALS_CURRENCY_TICKET); ?><br>
                        <span class="fw-bold">Total abonado:</span> $<?= number_format($paymentsTotalAmount, DECIMALS_CURRENCY_TICKET); ?><br>
                        <span class="fw-bold">Saldo pendiente:</span> $<?= number_format($pendingBalance, DECIMALS_CURRENCY_TICKET); ?>
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
                                  <?= getFilterActions($page_config['page_identifier']); ?>
                                </div>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>

                        <input name="sale_id" value="<?= md5($saleData->id); ?>" type="hidden">
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-12 mt-4">
                        <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="far fa-file-alt me-1"></i> Todas las ventas</h5>
                      </div>
                    </div>

                    <?php if (count($sales) == 0) : ?>
                      <div class="row">
                        <div class="col-12">
                          <div class="alert alert-info mb-0" role="alert">
                            <i class="mdi mdi-information-outline me-2"></i> No hay ventas realizadas a crédito para este cliente.
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>

                    <?php if (count($sales) > 0) : ?>
                      <div class="row">
                        <div class="col-12">
                          <div class="table-responsive min">
                            <table class="table table-xs">
                              <thead>
                                <tr>
                                  <th>#</th>
                                  <th>Folio</th>
                                  <th>Fecha</th>
                                  <th>Sucursal</th>
                                  <th class="text-end">Total</th>
                                  <th class="text-center">Estado</th>
                                  <th class="no-print"></th>
                                </tr>
                              </thead>

                              <tbody>
                                <?php foreach ($sales as $key => $sale) :
                                  $dataRow = [
                                    "id_venta"    => $sale["id_venta"],
                                    "sale_totalToPay"  => $sale["sale_totalToPay"],
                                    "sale_totalPaid"   => $sale["sale_totalPaid"],
                                    "sale_balance"     => $sale["sale_balance"],
                                    "sale_products"    => $sale["sale_products"]
                                  ];
                                ?>
                                  <tr class="align-middle">
                                    <td>
                                      <?= $key + 1; ?>
                                    </td>

                                    <td>
                                      <?= $sale["folio"]; ?>
                                    </td>

                                    <td>
                                      <?= date("d/m/Y", strtotime($sale["fecha_creacion"])); ?>
                                    </td>

                                    <td>
                                      <?= $sale["nombre_sucursal"]; ?>
                                    </td>

                                    <td class="text-end">
                                      $<?= number_format($sale["sale_totalToPay"], DECIMALS_CURRENCY_TICKET); ?>
                                    </td>

                                    <td class="text-center">
                                      <?php if ($sale["pagado"] == 'si') : ?>
                                        <span class="badge bg-success">Pagado</span>
                                      <?php else : ?>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                      <?php endif; ?>
                                    </td>

                                    <td class="text-end no-print">
                                      <?php if ($sale["pagado"] == "no") : ?>
                                        <button class="btn btn-primary btn-xs m-0 btn-add btn-add-payment btn-modal" data-row="<?= htmlentities(json_encode($dataRow)) ?>" data-bs-toggle="modal" data-bs-target="#ventas-pagos-modal" type="button">
                                          <small>
                                            <i class="fas fa-plus me-1"></i>
                                            Nuevo pago
                                          </small>
                                        </button>
                                      <?php endif; ?>

                                      <button class="btn btn-light btn-xs m-0 btn-show-products" data-row="<?= htmlentities(json_encode($dataRow)) ?>" data-bs-toggle="modal" data-bs-target="#<?= $page_config['page_identifier']; ?>-modal-ver-productos" type="button">
                                        <small>
                                          <i class="fas fa-eye me-1"></i>

                                          Ver Productos
                                        </small>
                                      </button>

                                      <a class="btn btn-light btn-xs m-0" target="_blank" href="<?= BASE_URL; ?>/ticket-venta?uid=<?= $sale["id_venta"]; ?>">
                                        <small>
                                          <i class="fas fa-print"></i>
                                          Imprimir
                                        </small>
                                      </a>
                                    </td>
                                  </tr>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                    <?php endif; ?>
                  </form>

                  <div class="col-12 mt-4">
                    <h5 class="mb-3 text-uppercase bg-light p-2 header-section-title"><i class="far fa-file-alt me-1"></i> Todos los pagos</h5>
                  </div>

                  <?php if (count($payments) == 0) : ?>
                    <div class="col-12">
                      <div class="alert alert-info mb-0" role="alert">
                        <i class="mdi mdi-information-outline me-2"></i> No hay pagos realizados para este cliente.
                      </div>
                    </div>
                  <?php endif; ?>

                  <?php if (count($payments) > 0) : ?>
                    <div class="col-12">
                      <div class="table-responsive min">
                        <table class="table table-xs">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Folio</th>
                              <th>Fecha</th>
                              <th>Notas</th>
                              <th class="text-end">Efectivo</th>
                              <th class="text-end">Cheque</th>
                              <th class="text-end">Transferencia</th>
                              <th class="text-end">Tarjeta de crédito</th>
                              <th class="text-end">Tarjeta de débito</th>
                              <th class="text-end">Total pagado</th>
                              <th class="no-print"></th>
                            </tr>
                          </thead>

                          <tbody>
                            <?php foreach ($payments as $key => $payment) : ?>
                              <tr class="align-middle">
                                <td>
                                  <?= $key + 1; ?>
                                </td>

                                <td>
                                  <?= $payment["folio"]; ?>
                                  <br>
                                  <small class="text-muted">Venta: <?= $payment["folio_venta"]; ?></small>
                                </td>

                                <td>
                                  <?= date("d/m/Y", strtotime($payment["fecha_hora"])); ?>
                                </td>

                                <td>
                                  <?= $payment["notas"] ? $payment["notas"] : "--"; ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["efectivo_monto"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["cheque_monto"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["transferencia_monto"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["tarjeta_credito_monto"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["tarjeta_debito_monto"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end">
                                  $<?= number_format($payment["monto_total"], DECIMALS_CURRENCY_TICKET); ?>
                                </td>

                                <td class="text-end no-print">
                                  <div class="btn-group">
                                    <a class="btn btn-primary btn-xs p-1" target="_blank" href="<?= BASE_URL; ?>/ticket-pago?uid=<?= md5($payment["id_venta_pago"]); ?>">
                                      <i class="fas fa-print"></i>
                                    </a>

                                    <button class="btn btn-danger btn-xs p-1 btn-delete-payment" data-uid="<?= $payment["id_venta_pago"]; ?>" type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar pago">
                                      <i class="fas fa-trash-alt"></i>
                                    </button>
                                  </div>
                                </td>
                              </tr>
                            <?php endforeach; ?>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  <?php endif; ?>
                  <!-- end row -->

                  <div class="mt-4 mb-1">
                    <div class="text-end d-print-none">
                      <a href="javascript:window.print()" class="btn btn-primary waves-effect waves-light me-1">
                        <i class="mdi mdi-printer me-1"></i> Imprimir
                      </a>

                      <a href="<?= BASE_URL; ?>/cuentas-por-cobrar" class="btn btn-secondary waves-effect waves-light me-1">
                        <i class="mdi mdi-arrow-left me-1"></i> Volver a cuentas por cobrar
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

    $(".btn-delete-payment").on("click", async function() {
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

    $(".btn-add-payment").on("click", function() {
      const dataRow = $(this).data("row");

      setTimeout(() => {
        $("#fd-monto").val(dataRow.sale_totalToPay);
        $("#fd-saldo").val(dataRow.sale_balance);
        $("#fd-nuevo-saldo").val(dataRow.sale_balance);
      }, 100);
    });

    $(".btn-show-products").on("click", function() {
      const dataRow = JSON.parse($(this).attr("data-row"));

      setTimeout(() => {
        $("#tabla-ver-productos").html(dataRow.sale_products);
      }, 100);
    });
  </script>
</body>

</html>