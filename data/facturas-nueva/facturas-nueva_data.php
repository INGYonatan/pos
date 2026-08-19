<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../lib/settings.inc.php';

require_once __DIR__ . "/../lib/facturacion/facturacion-moderna/facturacion-moderna.php";

//require __DIR__ . "/../lib/facturacion/fpdf17/fpdf.php";
require_once __DIR__ . "/../lib/facturacion/fpdf-1.86/fpdf.php";
require_once __DIR__ . "/../lib/facturacion/arrays.php";
require_once __DIR__ . "/../lib/facturacion/helpers.php";

require_once __DIR__ . "/../lib/helpers/emisores.helpers.php";

/**
 * Modelos de facturas
 */
require_once __DIR__ . "/../lib/models/facturas-ingreso.model.php";
require_once __DIR__ . "/../lib/models/facturas-pago.model.php";
require_once __DIR__ . "/../lib/models/facturas-anticipo-compra.model.php";
require_once __DIR__ . "/../lib/models/facturas-notas-credito.model.php";

$response = [
  "status"        => "error",
  "toastMessage"  => "¡Error inesperado!, intentalo nuevamente"
];

$facturacionHelpers = new FacturacionHelpers();

function moneyToCents($value): int
{
  return (int) round(((float) $value) * 100);
}

function centsToMoneyString(int $cents): string
{
  return number_format($cents / 100, 2, '.', '');
}

function decimalToUnits($value, int $decimals = 6): int
{
  $factor = pow(10, $decimals);
  return (int) round(((float) $value) * $factor);
}

function unitsToCents(int $units, int $decimals = 6): int
{
  $divider = pow(10, max(0, $decimals - 2));
  return (int) round($units / $divider);
}

$action     = $_POST["action"];
$identifier = "productos";

$user_data = getUserData(get_id_usuario());
$IS_ADMIN  = $user_data['IS_ADMIN'] == 'si' ? true : false;

if (!$IS_ADMIN) $_POST['id_sucursal'] = getSessionBranchOfficeId();

switch ($action) {
  case "search-invoice":
    $invoice = cleanStr($_POST["invoice"]);

    $query = "SELECT
        UUID
      FROM
        paal_facturas
      WHERE
        UUID = '$invoice'
      LIMIT 1
    ";

    $queryResult = mysqli_query($mysqli, $query);
    $numRows     = mysqli_num_rows($queryResult);

    if ($numRows == 0) $response["toastMessage"] = "¡No se encontró la factura!";

    if ($numRows > 0):
      $response = [
        "status"        => "success",
        "toastMessage"  => "¡Factura encontrada!",
      ];
    endif;
    break;

  case "create-invoice":
    $tipoFacturaLabel = $_POST['tipo_factura_label'];

    // $response = [
    //   "status" => "success",
    //   "title" => "datos",
    //   "alertMessage" => json_encode($_POST)
    // ];

    // break;

    # SUCURSAL
    $idSucursal   = $IS_ADMIN ? cleanStr($_POST['id_sucursal']) : getSessionBranchOfficeId();
    $dataSucursal = getBranchOfficeData($idSucursal);

    # SERIE FOLIO
    $folioSerie = $dataSucursal["numero_serie"] . INVOICE_SERIES_ABREVIATIONS[$tipoFacturaLabel];

    # Productos
    $productos = $_POST["productos"];

    if (!$productos) :
      $response["toastMessage"] = "¡No se han agregado productos!";
      echo json_encode($response);
      die;
    endif;

    $productos = json_decode($productos, true);

    if (sizeof($productos) == 0) :
      $response["toastMessage"] = "¡No se han agregado productos!";
      echo json_encode($response);
      die;
    endif;

    # Obtener datos del emisor
    $id_emisor  = $_POST['id_emisor'];
    $emisor     = new EmisoresHelper();
    $emisor->get($id_emisor);

    $dataEmisor = [
      "id_emisor"           => $emisor->getId(),
      "tipo"                => $emisor->getType(),
      "Nombre"              => $emisor->getBusinessName(),
      "RFC"                 => $emisor->getRfc(),
      "direccion"           => $emisor->getAddress(),
      "RegimenFiscalClave"  => $emisor->getFiscalRegimeId(),
      "ArchivoCer"          => $emisor->getCerFile(),
      "ArchivoKey"          => $emisor->getKeyFile(),
      "NumCertificado"      => $emisor->getCertificateNumber(),
      "RegimenFiscalText"   => $emisor->getFiscalRegimeText(),
      "PostalCode"          => $emisor->getPostalCode()
    ];

    //receptor
    $receptorNombre           = $_POST['razon_social'];
    $receptorRFC              = $_POST['cliente_rfc'];
    $receptorUsoCFDI          = $_POST['id_uso_cfdi'];
    $domicilioFiscalReceptor  = $_POST['cliente_domicilio_fiscal'];

    $regimenFiscalReceptor    = $_POST['id_regimen_fiscal'];

    $receptorUsoCFDIKey       = $facturacionHelpers->getUsoCFDIKey($receptorUsoCFDI);

    $receptorUsoCFDI          = $receptorUsoCFDIKey;
    $receptorUsoCFDIPDF       = $receptorUsoCFDIKey;
    $idCliente                =  isset($_POST['id_cliente']) ? $_POST['id_cliente'] : '0';

    // detalles del pago
    $metodoPago               = isset($_POST['metodo_pago'])    ? $_POST['metodo_pago']   : 'PUE';
    $formaPago                = $_POST['id_forma_pago'];

    if (!$formaPago) :
      $response["toastMessage"] = "¡Selecciona una forma de pago!";
      echo json_encode($response);
      die;
    endif;

    $formaPagoKey             = $facturacionHelpers->getFormaPagoKey($formaPago);
    $formaPagoLabel           = $facturacionHelpers->getFormaPagoLabel($formaPago);
    $formaPago                = $formaPagoKey;
    $formaPDF                 = $formaPagoLabel;
    $metodoPDF                = $arrayMetodosPagos[$metodoPago];

    //pagos resumen
    $subtotal                 = isset($_POST['subtotal'])       ? $_POST['subtotal']        : '0.00';
    $descuentoTotal           = isset($_POST['totalDescuento']) ? $_POST['totalDescuento']  : '0.00';
    $iepsTotal                = isset($_POST['totalIEPS'])      ? $_POST['totalIEPS']       : '0.00';
    $ivaTotal                 = isset($_POST['totalIVA'])       ? $_POST['totalIVA']        : '0.00';
    $total                    = isset($_POST['total'])          ? $_POST['total']           : '0.00';

    $tipoFactura              = $_POST['tipo_factura'];
    $fecha                    = date('Y-m-d', strtotime($_POST['fecha_emision']));
    $fechaFacturacion         = date('Y-m-d', strtotime($_POST['fecha_emision'])) . 'T' . date('H:i:s');
    $objetoImpuesto           = $_POST['objeto_impuesto'];
    $objetoImpuestoPDF        = $arrayObjetoImpuesto[$objetoImpuesto];

    $enviarCorreo = $_POST["enviar_al_correo"];

    if ($enviarCorreo == "si") :
      $correo = $_POST["correo"];

      $validarCorreo = filter_var($correo, FILTER_VALIDATE_EMAIL);

      if (!$validarCorreo) :
        $response["toastMessage"] = "¡Escribe un correo electrónico válido!";
        echo json_encode($response);
        die;
      endif;
    endif;

    // Resumen fiscal del frontend (fuente de verdad), normalizado a centavos.
    $subtotalCentsFromPost  = moneyToCents($_POST['subtotal'] ?? 0);
    $discountCentsFromPost  = moneyToCents($_POST['totalDescuento'] ?? 0);
    $iepsCentsFromPost      = moneyToCents($_POST['totalIEPS'] ?? 0);
    $ivaCentsFromPost       = moneyToCents($_POST['totalIVA'] ?? 0);
    $totalCentsFromPost     = moneyToCents($_POST['total'] ?? 0);

    // Total autoritativo: suma exacta en centavos de los componentes del resumen.
    $totalCentsFromSummary = $subtotalCentsFromPost - $discountCentsFromPost + $iepsCentsFromPost + $ivaCentsFromPost;

    if ($totalCentsFromPost !== $totalCentsFromSummary) {
      error_log("FACTURAS_SUMMARY_TOTAL_MISMATCH: posted={$totalCentsFromPost} summary={$totalCentsFromSummary}");
    }

    // Validación suave contra el detalle de conceptos para detectar desalineaciones de captura.
    $subtotalUnits = 0;
    $discountUnits = 0;
    $iepsUnits = 0;
    $ivaUnits = 0;

    foreach ($productos as $producto) {
      $subtotalUnits += decimalToUnits($producto['amountWithoutIVA'] ?? 0);
      $discountUnits += decimalToUnits($producto['discount'] ?? 0);
      $iepsUnits += decimalToUnits($producto['iepsCurrency'] ?? 0);
      $ivaUnits += decimalToUnits($producto['ivaCurrency'] ?? 0);
    }

    $subtotalCentsFromProducts = unitsToCents($subtotalUnits);
    $discountCentsFromProducts = unitsToCents($discountUnits);
    $iepsCentsFromProducts = unitsToCents($iepsUnits);
    $ivaCentsFromProducts = unitsToCents($ivaUnits);
    $totalCentsFromProducts = $subtotalCentsFromProducts - $discountCentsFromProducts + $iepsCentsFromProducts + $ivaCentsFromProducts;

    if (
      $subtotalCentsFromPost !== $subtotalCentsFromProducts ||
      $discountCentsFromPost !== $discountCentsFromProducts ||
      $iepsCentsFromPost !== $iepsCentsFromProducts ||
      $ivaCentsFromPost !== $ivaCentsFromProducts
    ) {
      error_log("FACTURAS_SUMMARY_PRODUCTS_MISMATCH: post={$subtotalCentsFromPost},{$discountCentsFromPost},{$iepsCentsFromPost},{$ivaCentsFromPost} products={$subtotalCentsFromProducts},{$discountCentsFromProducts},{$iepsCentsFromProducts},{$ivaCentsFromProducts}");
    }

    $subtotal       = centsToMoneyString($subtotalCentsFromPost);
    $descuentoTotal = centsToMoneyString($discountCentsFromPost);
    $iepsTotal      = centsToMoneyString($iepsCentsFromPost);
    $ivaTotal       = centsToMoneyString($ivaCentsFromPost);
    $total          = centsToMoneyString($totalCentsFromSummary);

    // $newProductos = [];

    // foreach ($productos as $producto) {
    //   $valorUnitario  = round($producto['priceWithoutIVA'], DECIMALS_CURRENCY);
    //   $descuento      = round($producto["discount"], DECIMALS_CURRENCY);
    //   $importe        = round($producto['amountWithoutIVA'], DECIMALS_CURRENCY);
    //   $iva            = round($producto['ivaCurrency'], DECIMALS_CURRENCY);

    //   $producto['valorUnitario'] = $valorUnitario;
    //   $producto['descuento']     = $descuento;
    //   $producto['importe']       = $importe;
    //   $producto['iva']           = $iva;

    //   array_push($newProductos, $producto);
    // }

    // $productos = $newProductos;

    if ($tipoFacturaLabel == "pago") require __DIR__ . "/facturas-pago.php";
    if ($tipoFacturaLabel == "ingreso") require __DIR__ . "/facturas-ingreso.php";
    if ($tipoFacturaLabel == "anticipo-de-compra") require __DIR__ . "/facturas-anticipo-de-compra.php";
    if ($tipoFacturaLabel == "nota-de-credito") require __DIR__ . "/facturas-nota-de-credito.php";

    /* if ($tipoFactura == 'I') require __DIR__ . "/facturas-ingreso.php";
    if ($tipoFactura == 'E') require __DIR__ . "/facturas-ingreso.php";
    if ($tipoFactura == 'P') require __DIR__ . "/facturas-pago.php"; */
    break;
};

echo json_encode($response);
mysqli_close($mysqli);
die;
