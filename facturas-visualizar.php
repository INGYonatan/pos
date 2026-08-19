<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/data/lib/settings.inc.php';

require_once __DIR__ . "/data/lib/facturacion/facturacion-moderna/facturacion-moderna.php";
require_once __DIR__ . "/data/lib/php-mailer/vendor/autoload.php";

require_once __DIR__ . "/data/lib/facturacion/fpdf-1.86/fpdf.php";
require_once __DIR__ . "/data/lib/facturacion/arrays.php";
require_once __DIR__ . "/data/lib/facturacion/helpers.php";

require_once __DIR__ . "/data/lib/helpers/emisores.helpers.php";

/**
 * Modelos de facturas
 */
require_once __DIR__ . "/data/lib/models/facturas-ingreso.model.php";
require_once __DIR__ . "/data/lib/models/facturas-pago.model.php";
require_once __DIR__ . "/data/lib/models/facturas-anticipo-compra.model.php";
require_once __DIR__ . "/data/lib/models/facturas-notas-credito.model.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

$facturacionHelpers = new FacturacionHelpers();

$action     = $_POST["action"];
$identifier = "productos";

$user_data  = getUserData(get_id_usuario());
$IS_ADMIN   = $user_data['IS_ADMIN'] == 'si' ? true : false;

if (!$IS_ADMIN) $_POST['id_sucursal'] = getSessionBranchOfficeId();

$tipoFacturaLabel = $_POST['tipo_factura_label'];

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

if ($tipoFacturaLabel == "pago")                require __DIR__ . "/__previews/facturas/pago.php";
if ($tipoFacturaLabel == "ingreso")             require __DIR__ . "/__previews/facturas/ingreso.php";
if ($tipoFacturaLabel == "anticipo-de-compra")  require __DIR__ . "/__previews/facturas/anticipo-de-compra.php";
if ($tipoFacturaLabel == "nota-de-credito")     require __DIR__ . "/__previews/facturas/nota-de-credito.php";
