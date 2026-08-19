<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/types.helper.php";

$page_config = [
  'page_title'        => 'Corte del mes',
  'page_identifier'   => 'corte-mes'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$filterMonth = isset($_GET['filterMonth']) ? $_GET['filterMonth'] : date('m');
$filterYear  = isset($_GET['filterYear']) ? $_GET['filterYear'] :  date('Y');

$filterDate = sprintf("%04d-%02d", $filterYear, $filterMonth);

$generateRandomColor = function () {
  $letters = '0123456789ABCDEF';
  $color   = '#';

  for ($i = 0; $i < 6; $i++) {
    $color .= $letters[rand(0, 15)];
  }

  return $color;
};

// Obtener todos los tipos de productos
$typesHelper = new TypesHelper();
$allTypes    = $typesHelper->getAll([
  "sortBy" => "tangible",
  "sortOrder" => "DESC"
])->data["rows"];

// Obtener las sucursales
$sucursales = [];
$byBranchId = $IS_ADMIN ? "1=1" : "id_sucursal = '" . getSessionBranchOfficeId() . "'";
$query      = "SELECT * FROM paal_sucursales WHERE status = 'activo' AND {$byBranchId} ORDER BY display_orden ASC";
$result     = mysqli_query($mysqli, $query);

while ($data = mysqli_fetch_assoc($result)) {
  // Obtener el total de la venta
  $branchId     = $data["id_sucursal"];
  $totals       = getContadoSaleTotalsByBranchIdAndDate($branchId, $filterDate);
  $creditTotals = getCreditoSaleTotalsByBranchIdAndDate($branchId, $filterDate);

  $efectivo = $totals["efectivo"] + $creditTotals["efectivo"];
  $debito   = $totals["tarjeta_debito"] + $creditTotals["tarjeta_debito"];
  $credito  = $totals["tarjeta_credito"] + $creditTotals["tarjeta_credito"];
  $transf   = $totals["transferencia"] + $creditTotals["transferencia"];
  $cheque   = $totals["cheque"]   + $creditTotals["cheque"];
  $total    = $totals["total"]    + $creditTotals["total"];
  $totalCredito = $creditTotals["total"];

  $branch = [
    "nombre"    => $data["nombre_sucursal"],
    "color"     => /* $generateRandomColor() */ "#333333",
    "total"     => number_format($total, DECIMALS_CURRENCY_TICKET),
    "efectivo"  => number_format($efectivo, DECIMALS_CURRENCY_TICKET),
    "debito"    => number_format($debito, DECIMALS_CURRENCY_TICKET),
    "credito"   => number_format($credito, DECIMALS_CURRENCY_TICKET),
    "transf"    => number_format($transf, DECIMALS_CURRENCY_TICKET),
    "cheque"    => number_format($cheque, DECIMALS_CURRENCY_TICKET),
    "subtotal"  => number_format($totals["subtotal"], DECIMALS_CURRENCY_TICKET),
    "iva"       => number_format($totals["iva"], DECIMALS_CURRENCY_TICKET),
    "ieps"      => number_format($totals["ieps"], DECIMALS_CURRENCY_TICKET),
    "total_mes" => number_format($total, DECIMALS_CURRENCY_TICKET),
    "total_abonos_credito" => number_format($totalCredito, DECIMALS_CURRENCY_TICKET),

    "por_depositar" => getContadoSalePorDepositar($branchId, $filterDate),

    "tipos" => []
  ];

  // Agregar los totales por tipo
  $totalSinEnvios = 0;

  foreach ($allTypes as $type) {
    /**
     * @var TypesHelper $type
     */
    $item = [
      "nombre"    => $type->getName(),
      "tangible"  => $type->getTangible(),
      "value"     => getTotalProductSaleByBranchIdAndDate($branchId, $filterDate, [
        "paymentForm" => "contado",
        "typeId" => $type->getId()
      ]),
      "count"     => getCountProductSaleByBranchIdAndDate($branchId, $filterDate, [
        "paymentForm" => "contado",
        "typeId" => $type->getId()
      ])
    ];

    if ($type->getTangible()) $totalSinEnvios += doubleval($item["value"]);

    $item["value"] = number_format($item["value"], DECIMALS_CURRENCY_TICKET);

    $branch["tipos"][] = $item;
  }

  // Sacar el promedio
  $grantTotal = $totals["total"];
  $daysInMonth = getCountDaysWithSalesByBranchIdAndDate($branchId, $filterDate, [
    "paymentForm" => "contado"
  ]);

  $branch["sin_envios"]   = number_format($totalSinEnvios, DECIMALS_CURRENCY_TICKET);
  $branch["promedio"] = $daysInMonth > 0 ? number_format($grantTotal / $daysInMonth, DECIMALS_CURRENCY_TICKET) : '0.00';

  $sucursales[] = $branch;
}

// Calcular totales consolidados
$totalConsolidado = [
  "nombre"    => "CONSOLIDADO",
  "color"     => "#d32f2f",
  "total"     => 0,
  "efectivo"  => 0,
  "debito"    => 0,
  "credito"   => 0,
  "transf"    => 0,
  "cheque"    => 0,
  "subtotal"  => 0,
  "iva"       => 0,
  "ieps"      => 0,
  "total_mes" => 0,
  "total_abonos_credito" => 0,
  "sin_envios" => 0,
  "por_depositar" => 0,
  "promedio"  => 0,
  "tipos"     => []
];

foreach ($sucursales as $sucursal) {
  $totalConsolidado["total"]        += (float)str_replace(",", "", $sucursal["total"]);
  $totalConsolidado["efectivo"]     += (float)str_replace(",", "", $sucursal["efectivo"]);
  $totalConsolidado["debito"]       += (float)str_replace(",", "", $sucursal["debito"]);
  $totalConsolidado["credito"]      += (float)str_replace(",", "", $sucursal["credito"]);
  $totalConsolidado["transf"]       += (float)str_replace(",", "", $sucursal["transf"]);
  $totalConsolidado["cheque"]       += (float)str_replace(",", "", $sucursal["cheque"]);
  $totalConsolidado["subtotal"]     += (float)str_replace(",", "", $sucursal["subtotal"]);
  $totalConsolidado["iva"]          += (float)str_replace(",", "", $sucursal["iva"]);
  $totalConsolidado["ieps"]         += (float)str_replace(",", "", $sucursal["ieps"]);
  $totalConsolidado["total_mes"]    += (float)str_replace(",", "", $sucursal["total_mes"]);
  $totalConsolidado["sin_envios"]   += (float)str_replace(",", "", $sucursal["sin_envios"]);
  $totalConsolidado["por_depositar"] += (float)str_replace(",", "", $sucursal["por_depositar"]);
  $totalConsolidado["promedio"]     += (float)str_replace(",", "", $sucursal["promedio"]);
  $totalConsolidado["total_abonos_credito"] += (float)str_replace(",", "", $sucursal["total_abonos_credito"]);

  // Acumular tipos de productos
  foreach ($sucursal["tipos"] as $type) {
    $tipoExistente = false;
    foreach ($totalConsolidado["tipos"] as &$tipoConsolidado) {
      if ($tipoConsolidado["nombre"] === $type["nombre"]) {
        $tipoConsolidado["value"] += (float)str_replace(",", "", $type["value"]);
        $tipoConsolidado["count"] += $type["count"];
        $tipoExistente = true;
        break;
      }
    }
    if (!$tipoExistente) {
      $totalConsolidado["tipos"][] = [
        "nombre"    => $type["nombre"],
        "tangible"  => $type["tangible"],
        "value"     => (float)str_replace(",", "", $type["value"]),
        "count"     => $type["count"]
      ];
    }
  }
}

$totalConsolidado["total"]        = number_format($totalConsolidado["total"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["efectivo"]     = number_format($totalConsolidado["efectivo"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["debito"]       = number_format($totalConsolidado["debito"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["credito"]      = number_format($totalConsolidado["credito"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["transf"]       = number_format($totalConsolidado["transf"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["cheque"]       = number_format($totalConsolidado["cheque"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["subtotal"]     = number_format($totalConsolidado["subtotal"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["iva"]          = number_format($totalConsolidado["iva"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["ieps"]         = number_format($totalConsolidado["ieps"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["total_mes"]    = number_format($totalConsolidado["total_mes"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["sin_envios"]   = number_format($totalConsolidado["sin_envios"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["por_depositar"] = number_format($totalConsolidado["por_depositar"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["promedio"]     = number_format($totalConsolidado["promedio"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["total_abonos_credito"] = number_format($totalConsolidado["total_abonos_credito"], DECIMALS_CURRENCY_TICKET);

// Formatear valores de tipos
foreach ($totalConsolidado["tipos"] as &$tipo) {
  $tipo["value"] = number_format($tipo["value"], DECIMALS_CURRENCY_TICKET);
}

/* 
[
  'nombre' => 'CORPORATIVO',
  'color' => '#222',
  'total' => '90,043.00',
  'efectivo' => '10,000.00',
  'debito' => '5,000.00',
  'credito' => '8,000.00',
  'transf' => '67,043.00',
  'cheque' => '0.00',
  'subtotal' => '90,043.00',
  'iva' => '14,407.00',
  'total_mes' => '104,450.00',
  'sin_envios' => '80,000.00',
  'envios' => '2,000.00',
  'varios' => '1,000.00',
  'equipos' => '3,000.00',
  'servicios' => '4,000.00',
  'por_depositar' => '5,000.00',
  'promedio' => '3,000.00',
  'envios_cant' => 2,
  'servicios_cant' => 3
],
*/
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
          <?php renderComponent("page-title", [
            "pageTitle"       => "Corte del mes",
            "pageDescription" => "Reporte de lo vendido en el mes"
          ]); ?>

          <form id="filters-form" class="row">
            <div class="col-12">
              <div class="card text-end">
                <div class="card-body d-flex justify-content-end p-2">
                  <div class="col-12 col-md-6 col-lg-4">
                    <div class="form-group text-end m-0">
                      <div class="input-group d-flex align-items-center">
                        <label class="form-label m-0 me-1" for="filter-dateDay">Fecha:</label>
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

          <div class="card">
            <div class="card-body">
              <div class="row gx-1 gy-2">


                <?php
                // $sucursales = [
                //   [
                //     'nombre' => 'CORPORATIVO',
                //     'color' => '#222',
                //     'total' => '90,043.00',
                //     'efectivo' => '10,000.00',
                //     'debito' => '5,000.00',
                //     'credito' => '8,000.00',
                //     'transf' => '67,043.00',
                //     'cheque' => '0.00',
                //     'subtotal' => '90,043.00',
                //     'iva' => '14,407.00',
                //     'total_mes' => '104,450.00',
                //     'sin_envios' => '80,000.00',
                //     'envios' => '2,000.00',
                //     'varios' => '1,000.00',
                //     'equipos' => '3,000.00',
                //     'servicios' => '4,000.00',
                //     'por_depositar' => '5,000.00',
                //     'promedio' => '3,000.00',
                //     'envios_cant' => 2,
                //     'servicios_cant' => 3
                //   ],
                //   ['nombre' => 'TUXTLA', 'color' => '#a3471b', 'total' => '55,000.00', 'efectivo' => '11,000.00', 'debito' => '7,000.00', 'credito' => '6,000.00', 'transf' => '31,000.00', 'cheque' => '0.00', 'subtotal' => '55,000.00', 'iva' => '8,800.00', 'total_mes' => '63,800.00', 'sin_envios' => '50,000.00', 'envios' => '1,000.00', 'varios' => '2,000.00', 'equipos' => '1,000.00', 'servicios' => '1,000.00', 'por_depositar' => '2,000.00', 'promedio' => '2,500.00', 'envios_cant' => 5, 'servicios_cant' => 12],
                //   ['nombre' => 'MERIDA', 'color' => '#a3471b', 'total' => '44,000.00', 'efectivo' => '5,000.00', 'debito' => '8,000.00', 'credito' => '7,000.00', 'transf' => '24,000.00', 'cheque' => '0.00', 'subtotal' => '44,000.00', 'iva' => '7,040.00', 'total_mes' => '51,040.00', 'sin_envios' => '40,000.00', 'envios' => '2,000.00', 'varios' => '1,000.00', 'equipos' => '500.00', 'servicios' => '500.00', 'por_depositar' => '1,000.00', 'promedio' => '2,200.00', 'envios_cant' => 20, 'servicios_cant' => 1],
                //   ['nombre' => 'VILLAHERMOSA', 'color' => '#a3471b', 'total' => '66,000.00', 'efectivo' => '15,000.00', 'debito' => '10,000.00', 'credito' => '11,000.00', 'transf' => '30,000.00', 'cheque' => '0.00', 'subtotal' => '66,000.00', 'iva' => '10,560.00', 'total_mes' => '76,560.00', 'sin_envios' => '60,000.00', 'envios' => '2,000.00', 'varios' => '2,000.00', 'equipos' => '1,000.00', 'servicios' => '1,000.00', 'por_depositar' => '3,000.00', 'promedio' => '2,800.00', 'envios_cant' => 20, 'servicios_cant' => 4],
                //   ['nombre' => 'CANCUN', 'color' => '#a3471b', 'total' => '77,000.00', 'efectivo' => '20,000.00', 'debito' => '12,000.00', 'credito' => '10,000.00', 'transf' => '35,000.00', 'cheque' => '0.00', 'subtotal' => '77,000.00', 'iva' => '12,320.00', 'total_mes' => '89,320.00', 'sin_envios' => '70,000.00', 'envios' => '3,000.00', 'varios' => '2,000.00', 'equipos' => '1,000.00', 'servicios' => '1,000.00', 'por_depositar' => '4,000.00', 'promedio' => '3,100.00', 'envios_cant' => 5, 'servicios_cant' => 5],
                //   ['nombre' => 'SAN CRISTOBAL', 'color' => '#a3471b', 'total' => '33,000.00', 'efectivo' => '5,000.00', 'debito' => '6,000.00', 'credito' => '5,000.00', 'transf' => '17,000.00', 'cheque' => '0.00', 'subtotal' => '33,000.00', 'iva' => '5,280.00', 'total_mes' => '38,280.00', 'sin_envios' => '30,000.00', 'envios' => '1,000.00', 'varios' => '1,000.00', 'equipos' => '500.00', 'servicios' => '500.00', 'por_depositar' => '1,500.00', 'promedio' => '1,800.00', 'envios_cant' => 2, 'servicios_cant' => 3],
                // ];
                $icons = [
                  'total' => '<i class="fas fa-clock"></i>',
                  'efectivo' => '<i class="fas fa-money-bill-wave"></i>',
                  'debito' => '<i class="fas fa-credit-card"></i>',
                  'credito' => '<i class="fab fa-cc-visa"></i>',
                  'transf' => '<i class="fas fa-exchange-alt"></i>',
                  'cheque' => '<i class="fas fa-money-check-alt"></i>',
                ];
                ?>
                <div style="overflow-x:auto; white-space:nowrap; width:100%;">
                  <div class="row w-100 gx-1 gy-2 mb-2 flex-nowrap" style="margin-left:0; margin-right:0; min-width:600px;">
                    <!-- TOTAL CONSOLIDADO -->
                    <div class="col-6 col-md-4 col-lg-3 px-1" style="display:inline-block; float:none; min-width:210px; max-width:280px; vertical-align:top;">
                      <div class="text-center fw-bold py-1 mb-1 d-flex align-items-center justify-content-center" style="color:<?= $totalConsolidado['color'] ?>; font-size:1.1em; background:#fff; border-radius:6px; letter-spacing:0.5px; line-height:1.1; border:2px solid <?= $totalConsolidado['color'] ?>; text-wrap: auto; height: 3.5rem;"> <?= $totalConsolidado['nombre'] ?> </div>
                      <div class="mb-2">
                        <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                          <span class="me-2" style="font-size:1.25em;"><?= $icons['efectivo'] ?></span>
                          <div>
                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Efectivo</div>
                            <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $totalConsolidado['efectivo'] ?></div>
                          </div>
                        </div>
                        <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                          <span class="me-2" style="font-size:1.25em;"><?= $icons['debito'] ?></span>
                          <div>
                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Débito</div>
                            <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $totalConsolidado['debito'] ?></div>
                          </div>
                        </div>
                        <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                          <span class="me-2" style="font-size:1.25em;"><?= $icons['credito'] ?></span>
                          <div>
                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Crédito</div>
                            <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $totalConsolidado['credito'] ?></div>
                          </div>
                        </div>
                        <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                          <span class="me-2" style="font-size:1.25em;"><?= $icons['transf'] ?></span>
                          <div>
                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Transferencia</div>
                            <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $totalConsolidado['transf'] ?></div>
                          </div>
                        </div>
                        <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                          <span class="me-2" style="font-size:1.25em;"><?= $icons['cheque'] ?></span>
                          <div>
                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Cheque</div>
                            <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $totalConsolidado['cheque'] ?></div>
                          </div>
                        </div>
                        <div class="rounded mb-1 px-2 py-2" style="background:#8fd694; color:#222; font-weight:bold;">
                          <div>SUBTOTAL: $<?= $totalConsolidado['subtotal'] ?></div>
                          <div>IVA: $<?= $totalConsolidado['iva'] ?></div>
                          <div>IEPS: $<?= $totalConsolidado['ieps'] ?></div>
                          <div>TOTAL MES: $<?= $totalConsolidado['total_mes'] ?></div>
                        </div>

                        <div class="rounded mb-1 px-2 py-2" style="background:#ffe066; color:#b85c00; font-weight:bold; font-size:0.98em;">
                          <div>Total solo productos<br><span style="color:#b85c00;">$<?= $totalConsolidado['sin_envios'] ?></span></div>

                          <?php foreach ($totalConsolidado["tipos"] as $type) :
                            $spanColor = $type["tangible"] ? "#d84315" : "#b85c00";
                          ?>
                            <div style="color:<?= $spanColor; ?>; font-weight:bold;"><?= $type["nombre"]; ?>: <span style="color:<?= $spanColor; ?>;"><?= $type['value'] ?></span></div>
                          <?php endforeach; ?>

                          <div style="color:#b85c00; font-weight:bold;">Total abonos credito: <span style="color:#b85c00;">$<?= $totalConsolidado['total_abonos_credito'] ?></span></div>
                        </div>

                        <div class="rounded mb-1 px-2 py-2" style="background:#ff4d4d; color:#fff; font-weight:bold; font-size:0.98em;">
                          Por depositar: $<?= $totalConsolidado['por_depositar'] ?>
                        </div>
                        <div class="rounded mb-1 px-2 py-2" style="background:#5b9bd5; color:#fff; font-weight:bold; font-size:0.98em;">
                          Promedio: $<?= $totalConsolidado['promedio'] ?>
                        </div>

                        <div class="rounded mb-1 px-2 py-2" style="background:#e3f0fa; color:#222; font-weight:bold; font-size:0.98em;">
                          <?php foreach ($totalConsolidado["tipos"] as $type) : ?>
                            <?php if ($type["tangible"]) continue; ?>

                            <?= $type["nombre"]; ?>: <?= $type["count"]; ?><br>
                          <?php endforeach; ?>
                        </div>
                      </div>
                    </div>
                    <?php foreach ($sucursales as $sucursal): ?>
                      <div class="col-6 col-md-4 col-lg-3 px-1" style="display:inline-block; float:none; min-width:210px; max-width:280px; vertical-align:top;">
                        <div class="text-center fw-bold py-1 mb-1 d-flex align-items-center justify-content-center" style="color:<?= $sucursal['color'] ?>; font-size:1.1em; background:#fff; border-radius:6px; letter-spacing:0.5px; line-height:1.1; border:1px solid #e0e0e0; text-wrap: auto; height: 3.5rem;"> <?= $sucursal['nombre'] ?> </div>
                        <div class="mb-2">
                          <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                            <span class="me-2" style="font-size:1.25em;"><?= $icons['efectivo'] ?></span>
                            <div>
                              <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Efectivo</div>
                              <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $sucursal['efectivo'] ?></div>
                            </div>
                          </div>
                          <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                            <span class="me-2" style="font-size:1.25em;"><?= $icons['debito'] ?></span>
                            <div>
                              <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Débito</div>
                              <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $sucursal['debito'] ?></div>
                            </div>
                          </div>
                          <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                            <span class="me-2" style="font-size:1.25em;"><?= $icons['credito'] ?></span>
                            <div>
                              <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Crédito</div>
                              <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $sucursal['credito'] ?></div>
                            </div>
                          </div>
                          <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                            <span class="me-2" style="font-size:1.25em;"><?= $icons['transf'] ?></span>
                            <div>
                              <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Transferencia</div>
                              <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $sucursal['transf'] ?></div>
                            </div>
                          </div>
                          <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                            <span class="me-2" style="font-size:1.25em;"><?= $icons['cheque'] ?></span>
                            <div>
                              <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Cheque</div>
                              <div style="font-size:1em; font-weight:700; line-height:1.1;">$<?= $sucursal['cheque'] ?></div>
                            </div>
                          </div>
                          <div class="rounded mb-1 px-2 py-2" style="background:#8fd694; color:#222; font-weight:bold;">
                            <div>SUBTOTAL: $<?= $sucursal['subtotal'] ?></div>
                            <div>IVA: $<?= $sucursal['iva'] ?></div>
                            <div>IEPS: $<?= $sucursal['ieps'] ?></div>
                            <div>TOTAL MES: $<?= $sucursal['total_mes'] ?></div>
                          </div>

                          <div class="rounded mb-1 px-2 py-2" style="background:#ffe066; color:#b85c00; font-weight:bold; font-size:0.98em;">
                            <div>Total solo productos<br><span style="color:#b85c00;">$<?= $sucursal['sin_envios'] ?></span></div>

                            <!-- <div style="color:#d84315; font-weight:bold;">Envíos: <span style="color:#d84315;">$<?= $sucursal['envios'] ?></span></div>
                            <div style="color:#d84315; font-weight:bold;">Varios: <span style="color:#d84315;">$<?= $sucursal['varios'] ?></span></div>
                            <div style="color:#d84315; font-weight:bold;">Equipos: <span style="color:#d84315;">$<?= $sucursal['equipos'] ?></span></div>
                            <div style="color:#b85c00; font-weight:bold;">Servicios: <span style="color:#b85c00;">$<?= $sucursal['servicios'] ?></span></div> -->

                            <?php foreach ($sucursal["tipos"] as $type) :
                              $spanColor = $type["tangible"] ? "#d84315" : "#b85c00";
                            ?>
                              <div style="color:<?= $spanColor; ?>; font-weight:bold;"><?= $type["nombre"]; ?>: <span style="color:<?= $spanColor; ?>;">$<?= $type['value'] ?></span></div>
                            <?php endforeach; ?>

                            <div style="color:#b85c00; font-weight:bold;">Total abonos credito: <span style="color:#b85c00;">$<?= $sucursal['total_abonos_credito'] ?></span></div>
                          </div>

                          <div class="rounded mb-1 px-2 py-2" style="background:#ff4d4d; color:#fff; font-weight:bold; font-size:0.98em;">
                            Por depositar: $<?= $sucursal['por_depositar'] ?>
                          </div>
                          <div class="rounded mb-1 px-2 py-2" style="background:#5b9bd5; color:#fff; font-weight:bold; font-size:0.98em;">
                            Promedio: $<?= $sucursal['promedio'] ?>
                          </div>

                          <div class="rounded mb-1 px-2 py-2" style="background:#e3f0fa; color:#222; font-weight:bold; font-size:0.98em;">
                            <!-- Envíos: <?= $sucursal['envios_cant'] ?><br>Servicios: <?= $sucursal['servicios_cant'] ?> -->
                            <?php foreach ($sucursal["tipos"] as $type) : ?>
                              <?php if ($type["tangible"]) continue; ?>

                              <?= $type["nombre"]; ?>: <?= $type["count"]; ?><br>
                            <?php endforeach; ?>
                          </div>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>

              </div>
            </div>
          </div>

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

  <script>
    $("#filters-form").on("change", "select", function() {
      $("#filters-form").submit();
    });
  </script>
</body>

</html>