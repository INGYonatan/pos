<?php
// ticket_deposit_report.php
// Genera un reporte listo para imprimirse en tamaño carta (Letter).
// Usa mysqli con la conexión existente en $mysqli (debe existir en el proyecto).
// Detecta prefijo de tablas si existe la variable $db_dti en el entorno.

require_once __DIR__ . '/inc/session.inc.php';

// --- CONFIGURACIÓN ---
// Si en tu app existe $db_dti (ej: 'dti'), lo usamos como prefijo para las tablas.
// Si no existe, se asume que las tablas son 'ventas' y 'facturas'.
$db_prefix = '';
if (isset($db_dti) && $db_dti !== '') {
  $db_prefix = $db_dti . '_';
}
$table_facturas = $db_prefix . 'facturas';
$table_ventas   = $db_prefix . 'ventas';

// Datos del banco (ajusta a los reales)
$bankData = [
  'bank_name'   => TICKET_BANK_NAME,
  'bank_group'  => TICKET_NAME,
  'bank_account' => TICKET_BANK_ACCOUNT,
];

// Parámetros GET (opcionales):
// start_date, end_date (YYYY-MM-DD), sucursal (id_sucursal), mode: 'efectivo' (default) o 'factura'
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : null;
$id_sucursal = isset($_GET['sucursal']) ? intval($_GET['sucursal']) : null;
$mode = isset($_GET['mode']) && $_GET['mode'] === 'factura' ? 'factura' : 'efectivo';

// Validaciones simples de formato de fecha (YYYY-MM-DD)
function valid_date($d)
{
  return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d);
}
if ($start_date && !valid_date($start_date)) {
  $start_date = null;
}
if ($end_date && !valid_date($end_date)) {
  $end_date = null;
}

// Campo a sumar según modo
$select_field = $mode === 'factura' ? 'f.total' : 'v.efectivo';

// Construir consulta (sanitizando con casting/validación)
$where = [];
// Sólo facturas vinculadas y no canceladas
$where[] = "f.id_venta IS NOT NULL";
$where[] = "f.cancelado = 0";
// Excluir ventas que ya tengan referencia de efectivo (nula o cadena vacía)
$where[] = "(v.efectivo_referencia IS NULL OR TRIM(v.efectivo_referencia) = '')";

// Filtros opcionales
if ($id_sucursal > 0) {
  $where[] = "f.id_sucursal = " . intval($id_sucursal);
}
if ($start_date) {
  $where[] = "DATE(f.fecha) >= '" . $mysqli->real_escape_string($start_date) . "'";
}
if ($end_date) {
  $where[] = "DATE(f.fecha) <= '" . $mysqli->real_escape_string($end_date) . "'";
}

$where_sql = implode(" AND ", $where);

// Consulta por fecha
$sql = "
SELECT
  DATE(f.fecha) AS fecha,
  DATE_FORMAT(f.fecha, '%d/%m/%Y') AS fecha_formateada,
  SUM($select_field) AS importe
FROM {$table_facturas} f
JOIN {$table_ventas} v ON v.id_venta = f.id_venta
WHERE {$where_sql}
GROUP BY DATE(f.fecha)
ORDER BY DATE(f.fecha) ASC
";

// Consulta total general
$sql_total = "
SELECT COALESCE(SUM($select_field), 0) AS total_general
FROM {$table_facturas} f
JOIN {$table_ventas} v ON v.id_venta = f.id_venta
WHERE {$where_sql}
";

// Ejecutar consultas
$result = $mysqli->query($sql);
if ($result === false) {
  die("Error en la consulta: " . $mysqli->error);
}
$result_total = $mysqli->query($sql_total);
if ($result_total === false) {
  die("Error en la consulta total: " . $mysqli->error);
}
$total_general_row = $result_total->fetch_assoc();
$total_general = isset($total_general_row['total_general']) ? (float)$total_general_row['total_general'] : 0.00;

// Helper para convertir número a texto (si en tu proyecto ya existe numtoletras se usará esa)
function numtoletras_fallback($cantidad)
{
  // fallback simple: muestra la cantidad en formato "X.XX PESOS"
  return strtoupper(number_format($cantidad, DECIMALS_CURRENCY)) . " PESOS";
}
$numero_en_letras = function ($cantidad) {
  if (function_exists('numtoletras')) {
    return numtoletras($cantidad);
  }
  return numtoletras_fallback($cantidad);
};

// Datos de cabecera de ejemplo. En tu proyecto sustituye con los datos reales.
$ADM_LOGO = defined('ADM_LOGO') ? ADM_LOGO : '/assets/img/logo.png';
$ADM_NAME = defined('ADM_NAME') ? ADM_NAME : 'MI EMPRESA S.A. DE C.V.';
$branchData = [
  "nombre_sucursal" => "Sucursal Principal",
  "direccion" => "Dirección de la sucursal"
];
$datetime = [
  "date" => date('d/m/Y'),
  "time" => date('h:i A')
];

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Depósitos a Realizar - Reporte</title>
  <style>
    /* Página tamaño carta para imprimir */
    @page {
      size: letter;
      margin: 25mm;
    }

    html,
    body {
      height: 100%;
      -webkit-print-color-adjust: exact;
    }

    body {
      margin: 0;
      font-family: Arial, Helvetica, sans-serif;
      color: #222;
      background: #fff;
      /* Para impresión en hoja carta usamos ancho completo de la página: */
      padding: 0;
    }

    /* Contenedor centrado para la impresión (margen controlado por @page) */
    .paper {
      width: 100%;
      max-width: 760px;
      /* aprox ancho util en carta con márgenes */
      margin: 0 auto;
      padding: 0;
      box-sizing: border-box;
      -webkit-box-sizing: border-box;
    }

    header.report-header {
      text-align: center;
      margin-bottom: 8px;
    }

    .bank-name {
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .bank-group {
      font-size: 12px;
      margin-top: 4px;
    }

    .bank-account {
      font-size: 12px;
      margin-top: 6px;
      font-weight: 700;
    }

    .report-meta {
      display: flex;
      justify-content: space-between;
      margin-top: 8px;
      margin-bottom: 8px;
      font-size: 12px;
    }

    .hr-strong {
      border-top: 2px solid #000;
      margin: 10px 0;
    }

    h1.title {
      text-align: center;
      letter-spacing: 8px;
      font-size: 18px;
      margin: 6px 0 10px;
    }

    table.report-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 12px;
      margin-bottom: 8px;
    }

    table.report-table thead th {
      border-bottom: 1px solid #000;
      padding: 8px 6px;
      text-align: left;
      font-weight: 700;
      background: #fff;
    }

    table.report-table tbody td {
      padding: 8px 6px;
      border-bottom: 1px solid #e6e6e6;
    }

    .col-date {
      width: 40%;
    }

    .col-amount {
      width: 60%;
      text-align: right;
    }

    .total-box {
      display: flex;
      justify-content: space-between;
      font-size: 14px;
      font-weight: 700;
      padding-top: 8px;
      border-top: 2px solid #000;
      margin-top: 6px;
    }

    .in-words {
      text-align: center;
      margin-top: 10px;
      font-weight: 700;
      font-size: 12px;
    }

    .footer-notes {
      font-size: 11px;
      text-align: center;
      margin-top: 14px;
      color: #333;
    }

    /* Evitar que filas se partan al imprimir */
    tr {
      page-break-inside: avoid;
    }

    /* Cabecera y pie fijos para imprimir cada página (puede o no repetirse según navegador) */
    header.report-header,
    footer.report-footer {
      position: running(header);
    }

    /* Botón imprimir oculto al imprimir */
    .print-btn {
      display: inline-block;
      margin: 12px;
      padding: 8px 14px;
      font-size: 14px;
      cursor: pointer;
    }

    @media print {
      .print-btn {
        display: none;
      }

      body {
        background: none;
      }

      .paper {
        box-shadow: none;
        margin: 0;
      }

      /* Mejorar separación entre tablas si hay salto de página */
      table.report-table {
        page-break-after: auto;
      }

      thead {
        display: table-header-group;
      }

      /* repetir header de tabla en cada hoja */
      tfoot {
        display: table-footer-group;
      }
    }

    /* Versión para pantalla: margen pequeño para previsualizar */
    .preview-container {
      padding: 10px;
    }
  </style>
</head>

<body>
  <div class="preview-container">
    <button class="print-btn" onclick="window.print()">Imprimir / Guardar PDF</button>
  </div>

  <div class="paper" role="document">
    <header class="report-header" aria-label="Encabezado del reporte">
      <div style="display:flex; align-items:center; justify-content:center; gap:12px;">
        <img src="<?= htmlspecialchars($ADM_LOGO); ?>" alt="logo" style="height:60px; object-fit:contain;">
      </div>

      <div style="margin-top:12px;">
        <div class="bank-name"><?= htmlspecialchars($bankData['bank_name']); ?></div>
        <div class="bank-group"><?= htmlspecialchars($bankData['bank_group']); ?></div>
        <div class="bank-account">CUENTA <?= htmlspecialchars($bankData['bank_account']); ?></div>
      </div>

      <div class="hr-strong" aria-hidden="true"></div>

      <div class="report-meta">
        <div>Fecha: <?= $datetime["date"]; ?></div>
        <div>Reporte: Depósitos a realizar</div>
        <div>Hora: <?= $datetime["time"]; ?></div>
      </div>

      <h1 class="title">D E P Ó S I T O S &nbsp; A &nbsp; R E A L I Z A R</h1>
    </header>

    <main>
      <table class="report-table" aria-label="Tabla de depósitos">
        <thead>
          <tr>
            <th class="col-date">FECHA</th>
            <th class="col-amount" style="text-align:right;">IMPORTE</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows === 0) {
            echo '<tr><td colspan="2" style="text-align:center; padding:12px 0;">No hay registros</td></tr>';
          } else {
            while ($row = $result->fetch_assoc()) {
              $importe = (float)$row['importe'];
              $fecha_form = $row['fecha_formateada'];
              echo '<tr>';
              echo '<td class="col-date">' . htmlspecialchars($fecha_form) . '</td>';
              echo '<td class="col-amount">$' . number_format($importe, DECIMALS_CURRENCY_TICKET) . '</td>';
              echo '</tr>';
            }
          }
          ?>
        </tbody>
      </table>

      <div class="total-box" role="status">
        <div>T O T A L</div>
        <div>$<?= number_format($total_general, DECIMALS_CURRENCY_TICKET); ?></div>
      </div>

      <div class="in-words">
        <?= htmlspecialchars($numero_en_letras($total_general)); ?>
      </div>

      <div class="footer-notes">
        (Facturas timbradas y no canceladas; sin referencia de efectivo) <br>
        <?= $mode === 'factura' ? 'Importes según total de factura' : 'Importes según efectivo registrado en la venta'; ?>
      </div>
    </main>

    <footer style="margin-top:18mm; text-align:center; font-size:11px; color:#555;">
      Reporte generado por sistema - <?= $datetime["date"]; ?> a las <?= $datetime["time"]; ?>
    </footer>
  </div>
</body>

</html>