<?php
/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- BASE URL
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('URL_SCHEME', getWebSiteProtocol());
define('HOST_URL', getServerName(SUBDOMAIN));
define('BASE_URL', URL_SCHEME .  HOST_URL);
define('BASE_PATH', getBasePath(SUBDOMAIN));

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- ADM_CONFIG
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('ADM_IDENTIFIER', $_ENV["ADM_IDENTIFIER"]);
define('ADM_NAME', $_ENV["ADM_NAME"]);

define("TICKET_NAME", $_ENV["TICKET_NAME"]);
define("TICKET_BANK_NAME", $_ENV["TICKET_BANK_NAME"]);
define("TICKET_BANK_ACCOUNT", $_ENV["TICKET_BANK_ACCOUNT"]);

define('ADM_WEBPAGE', $_ENV["ADM_WEBPAGE"]);
define("ADM_EMAIL", $_ENV["ADM_EMAIL"]);
define('ADM_PHONE', $_ENV["ADM_PHONE"]);

# ASSETS
define("SYSTEM_MEDIA_PATH", $_ENV["SYSTEM_MEDIA_PATH"]);
define("SYSTEM_MEDIA_PATH_URL", BASE_URL . "/" . SYSTEM_MEDIA_PATH);
define("SYSTEM_MEDIA_PATH_PATH", BASE_PATH . "/" . SYSTEM_MEDIA_PATH);

// define('ADM_LOGO', BASE_URL . '/src/assets/images/logo-colores.png');
// define('ADM_LOGO_WHITE', BASE_URL . '/src/assets/images/logo.png');
// define('ADM_FAVICON', BASE_URL . '/src/assets/images/favicon.png');

define('ADM_LOGO', SYSTEM_MEDIA_PATH_URL . "/" . $_ENV["ADM_LOGO"]);
define('ADM_LOGO_WHITE', SYSTEM_MEDIA_PATH_URL . "/" . $_ENV["ADM_LOGO_WHITE"]);
define('ADM_FAVICON', SYSTEM_MEDIA_PATH_URL . "/" . $_ENV["ADM_FAVICON"]);
define('ADM_LOGO_WIDTH_LARGE', SYSTEM_MEDIA_PATH_URL . "/" . $_ENV["ADM_LOGO_WIDTH_LARGE"]);

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- PDF/TICKET CONFIG
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define("PDF_LOGO", ADM_LOGO);
define("PDF_LOGO_HEIGHT", $_ENV["PDF_LOGO_HEIGHT_PX"]);
define("PDF_LOGO_WIDTH", $_ENV["PDF_LOGO_WIDTH_PX"]);


/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- START INVOICE CONFIG
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
const INVOICE_LOGO = ADM_LOGO;

define("INVOICE_LOGO_HEIGHT", $_ENV["PDF_LOGO_HEIGHT_MM"]); // En mm
define("INVOICE_LOGO_WIDTH", $_ENV["PDF_LOGO_WIDTH_MM"]); // En mm

$invoiceFooter = [ADM_EMAIL, formatPhoneNumber(ADM_PHONE), ADM_WEBPAGE];
$invoiceFooter = implode(" | ", $invoiceFooter);

define("INVOICE_FOOTER", $invoiceFooter);
/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- START INVOICE CONFIG
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

//define("PDF_ADDRESS", "CALZADA DE LOS HEROES ESQ. MARIANO BALLEZA S/N 37800 CENTRO DOLORES HIDALGO, GUANAJUATO");
//define("PDF_PHONE", "4181825246");
//define("PDF_WHATSAPP", "4181825246");
//define("PDF_EMAIL", "");

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- MYSQLI PASSWORD SECRET
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('MYSQLI_PASSWORD_SECRET', $_ENV["MYSQLI_PASSWORD_SECRET"]);

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- PHPMAILER CONFIG
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('PHPMAILER_HOST', $_ENV["PHPMAILER_HOST"]);

# PHPMAILER SUPPORT MAIL
// define('PHPMAILER_SUPPORT_EMAIL', 'soporte@cocinaspaal.com');
// define('PHPMAILER_SUPPORT_PASSWORD', 'W*T.h(&+9*p*');

define('PHPMAILER_SUPPORT_EMAIL', $_ENV["PHPMAILER_SUPPORT_EMAIL"]);
define('PHPMAILER_SUPPORT_PASSWORD', $_ENV["PHPMAILER_SUPPORT_PASSWORD"]);

define('PHPMAILER_SALES_EMAIL', $_ENV["PHPMAILER_SALES_EMAIL"]);
define('PHPMAILER_SALES_PASSWORD', $_ENV["PHPMAILER_SALES_PASSWORD"]);

define("SALE_EQUIPMENT_NAME", $_ENV["PHPMAILER_SALE_EQUIPMENT_NAME"]);
define("SALE_EQUIPMENT_EMAIL", $_ENV["PHPMAILER_SALE_EQUIPMENT_EMAIL"]);

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- TYPES
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('TIPO_MOVIMIENTO_INCREMENTO', 'incremento');
define('TIPO_MOVIMIENTO_DECREMENTO', 'decremento');

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- SESSIONS
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('SESSION_CARRITO_POS', 'ssid_pos_carrito');

define('SESSION_CARRITO_NUEVA_ORDEN_COMPRA', 'ssid_nueva_orden_compra_carrito');
define('SESSION_CARRITO_EDITAR_ORDEN_COMPRA', 'ssid_editar_orden_compra_carrito');

define('SESSION_CARRITO_NUEVA_COMPRA', 'ssid_nueva_compra_carrito');
define('SESSION_CARRITO_EDITAR_COMPRA', 'ssid_editar_compra_carrito');

define('SESSION_CARRITO_NUEVA_COTIZACION', 'ssid_nueva_cotizacion_carrito');
define('SESSION_CARRITO_EDITAR_COTIZACION', 'ssid_editar_cotizacion_carrito');
define('SESSION_CARRITO_COTIZACION_A_VENTA', 'ssid_cotizacion_a_venta_carrito');

define('SESSION_CARRITO_AJUSTES_INVENTARIO', 'ssid_ajustes_inventario_carrito');

define('SESSION_CARRITO_TRANSFERIR_INVENTARIO', 'ssid_transferir_inventario_carrito');
define('SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_ORIGEN', 'ssid_transferir_inventario_carrito_sucursal_origen');
define('SESSION_CARRITO_TRANSFERIR_INVENTARIO_SUCURSAL_DESTINO', 'ssid_transferir_inventario_carrito_sucursal_destino');

define("SESSION_CARRITO_SOLICITUD_TRASPASO", "ssid_solicitud_traspaso_carrito");
define("SESSION_CARRITO_SOLICITUD_TRASPASO_SUCURSAL_ORIGEN", "ssid_solicitud_traspaso_carrito_sucursal_origen");
define("SESSION_CARRITO_SOLICITUD_TRASPASO_SUCURSAL_DESTINO", "ssid_solicitud_traspaso_carrito_sucursal_destino");

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- ACCIONES_KARDEX
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('ACCION_NUEVO_PRODUCTO', 'Alta de producto');

define('ACCION_NUEVA_ORDEN_COMPRA', 'Nueva orden de compra realizada');
define('ACCION_ORDEN_COMPRA_A_COMPRA', 'Orden de compra convertida en compra');

define('ACCION_NUEVA_COMPRA', 'Nueva compra realizada');
define('ACCION_CANCELAR_COMPRA', 'Compra cancelada');

define('ACCION_INVENTARIO_AUMENTAR_STOCK', 'Ajuste (Incremento) realizado');
define('ACCION_INVENTARIO_REDUCIR_STOCK', 'Ajuste (Decremento) realizado');

define('ACCION_INVENTARIO_TRANSFERIR', 'Transferencia de productos');

define('ACCION_VENTA', 'Venta realizada');
define('ACCION_COTIZACION_A_VENTA', 'Cotización convertida a venta');

define('ACCION_CANCELAR_AJUSTE', 'Ajuste de inventario cancelado');
define('ACCION_CANCELAR_TRANSFERENCIA', 'Transferencia cancelada');
define('ACCION_CANCELAR_VENTA', 'Venta cancelada');

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- USER SESSION
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('COOKIE_SESSION_COOKIE_NAME', ADM_IDENTIFIER . 'cfe9strssuid');
define('COOKIE_SESSION_USER_COOKIE_NAME', ADM_IDENTIFIER . 'cfe9str_ssuid');
define('COOKIE_SESSION_BOT_COOKIE_NAME', ADM_IDENTIFIER . 'llbotcte_sse_bot');

/* ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//-- GENERAL
:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
define('TICKET_RFC', 'ICN0309047k7');


define('QUOTE_DAYS_TO_EXPIRED', 15);

const INVOICE_TYPES_ABREVIATIONS = [
  "ingreso"             => "I",
  "pago"                => "P",
  "anticipo-de-compra"  => "I",
  "nota-de-credito"     => "E",
  "traslado"            => "T"
];

const INVOICE_SERIES_ABREVIATIONS = [
  "ingreso"             => "i",
  "pago"                => "p",
  "anticipo-de-compra"  => "ant",
  "nota-de-credito"     => "nc",
  "traslado"            => "t"
];

/**
 * -------------------------------------------------------------------------------------------------
 */

const CARPETA_FACTURAS_INGRESO_URL              = BASE_URL . "/src/assets/facturacion/comprobantes/ingreso/";
const CARPETA_FACTURAS_INGRESO_PATH             = BASE_PATH . "/src/assets/facturacion/comprobantes/ingreso/";

const CARPETA_FACTURAS_ANTICIPO_DE_COMPRA_URL   = BASE_URL . "/src/assets/facturacion/comprobantes/anticipo-de-compra/";
const CARPETA_FACTURAS_ANTICIPO_DE_COMPRA_PATH  = BASE_PATH . "/src/assets/facturacion/comprobantes/anticipo-de-compra/";

const CARPETA_FACTURAS_NOTA_DE_CREDITO_URL      = BASE_URL . "/src/assets/facturacion/comprobantes/nota-de-credito/";
const CARPETA_FACTURAS_NOTA_DE_CREDITO_PATH     = BASE_PATH . "/src/assets/facturacion/comprobantes/nota-de-credito/";

const CARPETA_FACTURAS_PAGO_URL                 = BASE_URL . "/src/assets/facturacion/comprobantes/pago/";
const CARPETA_FACTURAS_PAGO_PATH                = BASE_PATH . "/src/assets/facturacion/comprobantes/pago/";

const CARPETA_FACTURAS_TRASLADO_URL             = BASE_URL . "/src/assets/facturacion/comprobantes/traslado/";
const CARPETA_FACTURAS_TRASLADO_PATH            = BASE_PATH . "/src/assets/facturacion/comprobantes/traslado/";

/**
 * -------------------------------------------------------------------------------------------------
 */

const FACTURAS_CERTIFICADO_URL                  = BASE_URL . "/src/assets/facturacion/utilerias/certificados/";
const FACTURAS_CERTIFICADO_PATH                 = BASE_PATH . "/src/assets/facturacion/utilerias/certificados/";

const FACTURAS_SELLO_XSLT40_URL                 = BASE_URL . "/src/assets/facturacion/utilerias/xslt40/cadenaoriginal_4_0.xslt";
const FACTURAS_SELLO_XSLT40_PATH                = BASE_PATH . "/src/assets/facturacion/utilerias/xslt40/cadenaoriginal_4_0.xslt";

/**
 * -------------------------------------------------------------------------------------------------
 */

define("FACTURAS_URL_TIMBRADO", $_ENV["FACTURAS_URL_TIMBRADO"]);
define("FACTURAS_ID_USUARIO", $_ENV["FACTURAS_ID_USUARIO"]);
define("FACTURAS_PASSWORD", $_ENV["FACTURAS_PASSWORD"]);

/**
 * -------------------------------------------------------------------------------------------------
 */
const DECIMALS_CURRENCY_TICKET = 2;

// CATÁLOGOS DE FATURACIÓN GLOBAL

const FACTURA_GLOBAL_PERIODICIDAD = [
  "01" => "Diario",
  "02" => "Semanal",
  "03" => "Quincenal",
  "04" => "Mensual",
  // "05" => "Bimestral"
];

const FACTURA_GLOBAL_MESES = [
  "01" => "Enero",
  "02" => "Febrero",
  "03" => "Marzo",
  "04" => "Abril",
  "05" => "Mayo",
  "06" => "Junio",
  "07" => "Julio",
  "08" => "Agosto",
  "09" => "Septiembre",
  "10" => "Octubre",
  "11" => "Noviembre",
  "12" => "Diciembre",
  // "13" => "Enero-Febrero",
  // "14" => "Marzo-Abril",
  // "15" => "Mayo-Junio",
  // "16" => "Julio-Agosto",
  // "17" => "Septiembre-Octubre",
  // "18" => "Noviembre-Diciembre"
];
