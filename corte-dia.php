<?php
include 'inc/session.inc.php';
require_once __DIR__ . "/data/lib/helpers/types.helper.php";

$page_config = [
    'page_title'        => 'Corte del día',
    'page_identifier'   => 'corte-dia'
];

checkModuleActionPermission($page_config['page_identifier'], 'ver', true);

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$filterDay    = $_GET["filterDay"]    ? $_GET["filterDay"]    : date("d");
$filterMonth  = $_GET["filterMonth"]  ? $_GET["filterMonth"]  : date("m");
$filterYear   = $_GET["filterYear"]   ? $_GET["filterYear"]   : date("Y");

$months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

$monthDays = (int)date('t', mktime(0, 0, 0, (int)$filterMonth, 1, (int)$filterYear));

$filterDate = sprintf("%04d-%02d-%02d", $filterYear, $filterMonth, $filterDay);

/* 
[
                                        'nombre' => 'CORPORATIVO',
                                        'color' => '#222',
                                        'total' => '9,043.00',
                                        'efectivo' => '0.00',
                                        'debito' => '0.00',
                                        'credito' => '0.00',
                                        'transf' => '9,043.00',
                                        'cheque' => '0.00'
                                    ],
*/

// Obtener las sucursales
$sucursales = [];

$byBranchId = $IS_ADMIN ? "1=1" : "id_sucursal = '" . getSessionBranchOfficeId() . "'";
$query      = "SELECT * FROM paal_sucursales WHERE status = 'activo' AND {$byBranchId} ORDER BY display_orden ASC";
$result     = mysqli_query($mysqli, $query);

while ($data = mysqli_fetch_assoc($result)) {
    // Obtener el total de la venta
    $branchId = $data["id_sucursal"];
    $totals   = getContadoSaleTotalsByBranchIdAndDate($branchId, $filterDate, [
        "dateMode" => "%Y-%m-%d"
    ]);

    $creditTotals = getCreditoSaleTotalsByBranchIdAndDate($branchId, $filterDate, [
        "dateMode" => "%Y-%m-%d"
    ]);

    $efectivo = $totals["efectivo"] + $creditTotals["efectivo"];
    $debito   = $totals["tarjeta_debito"] + $creditTotals["tarjeta_debito"];
    $credito  = $totals["tarjeta_credito"] + $creditTotals["tarjeta_credito"];
    $transf   = $totals["transferencia"] + $creditTotals["transferencia"];
    $cheque   = $totals["cheque"]   + $creditTotals["cheque"];
    $total    = $totals["total"]    + $creditTotals["total"];

    $branch = [
        "nombre"    => $data["nombre_sucursal"],
        "color"     => /* $generateRandomColor() */ "#333333",
        "total"     => number_format($total, DECIMALS_CURRENCY_TICKET),
        "efectivo"  => number_format($efectivo, DECIMALS_CURRENCY_TICKET),
        "debito"    => number_format($debito, DECIMALS_CURRENCY_TICKET),
        "credito"   => number_format($credito, DECIMALS_CURRENCY_TICKET),
        "transf"    => number_format($transf, DECIMALS_CURRENCY_TICKET),
        "cheque"    => number_format($cheque, DECIMALS_CURRENCY_TICKET),
    ];

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
];

foreach ($sucursales as $sucursal) {
    $totalConsolidado["total"]    += (float)str_replace(",", "", $sucursal["total"]);
    $totalConsolidado["efectivo"] += (float)str_replace(",", "", $sucursal["efectivo"]);
    $totalConsolidado["debito"]   += (float)str_replace(",", "", $sucursal["debito"]);
    $totalConsolidado["credito"]  += (float)str_replace(",", "", $sucursal["credito"]);
    $totalConsolidado["transf"]   += (float)str_replace(",", "", $sucursal["transf"]);
    $totalConsolidado["cheque"]   += (float)str_replace(",", "", $sucursal["cheque"]);
}

$totalConsolidado["total"]    = number_format($totalConsolidado["total"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["efectivo"] = number_format($totalConsolidado["efectivo"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["debito"]   = number_format($totalConsolidado["debito"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["credito"]  = number_format($totalConsolidado["credito"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["transf"]   = number_format($totalConsolidado["transf"], DECIMALS_CURRENCY_TICKET);
$totalConsolidado["cheque"]   = number_format($totalConsolidado["cheque"], DECIMALS_CURRENCY_TICKET);

$icons = [
    'total' => '<i class="fas fa-clock"></i>',
    'efectivo' => '<i class="fas fa-money-bill-wave"></i>',
    'debito' => '<i class="fas fa-credit-card"></i>',
    'credito' => '<i class="fab fa-cc-visa"></i>',
    'transf' => '<i class="fas fa-exchange-alt"></i>',
    'cheque' => '<i class="fas fa-money-check-alt"></i>',
];

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
                        "pageTitle"       => "Corte del día",
                        "pageDescription" => "Reporte de lo vendido en el día"
                    ]); ?>

                    <form id="filters-form" class="row">
                        <div class="col-12">
                            <div class="card text-end">
                                <div class="card-body d-flex flex-column flex-lg-row justify-content-end p-2 gap-3">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <div class="form-group text-end m-0">
                                            <div class="input-group d-flex align-items-center">
                                                <label class="form-label m-0 me-1" for="filter-dateDay">Fecha:</label>

                                                <select id="filter-dateDay" class="form-control form-select" name="filterDay" required>
                                                    <option value="">Día</option>
                                                    <?php for ($i = 1; $i <= $monthDays; $i++) : ?>
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
                    </form>
                    <div class="card">
                        <div class="card-body">

                            <div class="row gx-1 gy-2">
                                <?php
                                // $sucursaless = [
                                //     [
                                //         'nombre' => 'CORPORATIVO',
                                //         'color' => '#222',
                                //         'total' => '9,043.00',
                                //         'efectivo' => '0.00',
                                //         'debito' => '0.00',
                                //         'credito' => '0.00',
                                //         'transf' => '9,043.00',
                                //         'cheque' => '0.00'
                                //     ],
                                //     [
                                //         'nombre' => 'TUXTLA',
                                //         'color' => '#a3471b',
                                //         'total' => '5,000.00',
                                //         'efectivo' => '1,000.00',
                                //         'debito' => '500.00',
                                //         'credito' => '500.00',
                                //         'transf' => '3,000.00',
                                //         'cheque' => '0.00'
                                //     ],
                                //     ['nombre' => 'MERIDA', 'color' => '#a3471b', 'total' => '4,000.00', 'efectivo' => '500.00', 'debito' => '500.00', 'credito' => '500.00', 'transf' => '2,500.00', 'cheque' => '0.00'],
                                //     ['nombre' => 'VILLAHERMOSA', 'color' => '#a3471b', 'total' => '6,000.00', 'efectivo' => '1,500.00', 'debito' => '1,000.00', 'credito' => '1,000.00', 'transf' => '2,500.00', 'cheque' => '0.00'],
                                //     ['nombre' => 'CANCUN', 'color' => '#a3471b', 'total' => '7,000.00', 'efectivo' => '2,000.00', 'debito' => '1,000.00', 'credito' => '1,000.00', 'transf' => '3,000.00', 'cheque' => '0.00'],
                                //     ['nombre' => 'SAN CRISTOBAL', 'color' => '#a3471b', 'total' => '3,000.00', 'efectivo' => '500.00', 'debito' => '500.00', 'credito' => '500.00', 'transf' => '1,500.00', 'cheque' => '0.00'],
                                // ];
                                // $icons = [
                                //     'total' => '<i class="fas fa-clock"></i>',
                                //     'efectivo' => '<i class="fas fa-money-bill-wave"></i>',
                                //     'debito' => '<i class="fas fa-credit-card"></i>',
                                //     'credito' => '<i class="fab fa-cc-visa"></i>',
                                //     'transf' => '<i class="fas fa-exchange-alt"></i>',
                                //     'cheque' => '<i class="fas fa-money-check-alt"></i>',
                                // ];
                                // $labels = [
                                //     'total' => 'Total',
                                //     'efectivo' => 'Efectivo',
                                //     'debito' => 'T. Débito',
                                //     'credito' => 'T. Crédito',
                                //     'transf' => 'Transferencia',
                                //     'cheque' => 'Cheque',
                                // ];
                                ?>
                                <div style="overflow-x:auto; white-space:nowrap; width:100%;">
                                    <div class="row w-100 gx-1 gy-2 flex-nowrap" style="margin-left:0; margin-right:0; min-width:600px;">
                                        <!-- TOTAL CONSOLIDADO -->
                                        <div class="col-6 col-md-4 col-lg-2 px-1" style="display:inline-block; float:none; min-width:170px; max-width:280px; vertical-align:top;">
                                            <div class="text-center fw-bold py-1 mb-1 d-flex align-items-center justify-content-center" style="color:<?= $totalConsolidado['color'] ?>; font-size:1.1em; background:#fff; border-radius:6px; letter-spacing:0.5px; line-height:1.1; border:2px solid <?= $totalConsolidado['color'] ?>; text-wrap: auto; height: 3.5rem;"> <?= $totalConsolidado['nombre'] ?> </div>

                                            <div class="mb-2">
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#07b15a; color:#fff;">
                                                    <span class="me-2" style="font-size:1.5em;"><?= $icons['total'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.15em; font-weight:600; line-height:1.1;">Total</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['total'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                    <span class="me-2" style="font-size:1.25em;"><?= $icons['efectivo'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Efectivo</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['efectivo'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                    <span class="me-2" style="font-size:1.25em;"><?= $icons['debito'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Débito</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['debito'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                    <span class="me-2" style="font-size:1.25em;"><?= $icons['credito'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Crédito</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['credito'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                    <span class="me-2" style="font-size:1.25em;"><?= $icons['transf'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Transferencia</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['transf'] ?></div>
                                                    </div>
                                                </div>
                                                <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                    <span class="me-2" style="font-size:1.25em;"><?= $icons['cheque'] ?></span>
                                                    <div>
                                                        <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Cheque</div>
                                                        <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $totalConsolidado['cheque'] ?></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php foreach ($sucursales as $sucursal): ?>
                                            <div class="col-6 col-md-4 col-lg-2 px-1" style="display:inline-block; float:none; min-width:170px; max-width:280px; vertical-align:top;">
                                                <div class="text-center fw-bold py-1 mb-1 d-flex align-items-center justify-content-center" style="color:<?= $sucursal['color'] ?>; font-size:1.1em; background:#fff; border-radius:6px; letter-spacing:0.5px; line-height:1.1; border:1px solid #e0e0e0; text-wrap: auto; height: 3.5rem;"> <?= $sucursal['nombre'] ?> </div>

                                                <div class="mb-2">
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#07b15a; color:#fff;">
                                                        <span class="me-2" style="font-size:1.5em;"><?= $icons['total'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.15em; font-weight:600; line-height:1.1;">Total</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['total'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                        <span class="me-2" style="font-size:1.25em;"><?= $icons['efectivo'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Efectivo</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['efectivo'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                        <span class="me-2" style="font-size:1.25em;"><?= $icons['debito'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Débito</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['debito'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                        <span class="me-2" style="font-size:1.25em;"><?= $icons['credito'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">T. Crédito</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['credito'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                        <span class="me-2" style="font-size:1.25em;"><?= $icons['transf'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Transferencia</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['transf'] ?></div>
                                                        </div>
                                                    </div>
                                                    <div class="rounded mb-1 d-flex align-items-center px-2 py-2" style="background:#f6a623; color:#fff;">
                                                        <span class="me-2" style="font-size:1.25em;"><?= $icons['cheque'] ?></span>
                                                        <div>
                                                            <div style="font-size:1.05em; font-weight:600; line-height:1.1;">Cheque</div>
                                                            <div style="font-size:1em; font-weight:700; line-height:1.1;"><?= $sucursal['cheque'] ?></div>
                                                        </div>
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