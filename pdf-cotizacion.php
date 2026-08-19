<?php
require 'inc/session.inc.php';
require_once 'data/lib/tcpdf/vendor/autoload.php';
require 'data/pdf/cotizacion/pdf.php';
require 'data/lib/helpers/quotes.helper.php';

$quote_id = cleanStr($_GET['uid']);

if (empty($quote_id)) :
  closeSession();
  die;
endif;

$data                   = get_quote_data($quote_id);
$branchData             = getBranchOfficeData($data->branch_id);

$quote                  = new stdClass();

$company                = new stdClass();
$seller                 = new stdClass();
$customer               = new stdClass();
$products               = new stdClass();
$bank                   = new stdClass();

$company->logo          = PDF_LOGO;
$company->name          = ADM_NAME;
$company->branch        = $branchData["nombre_sucursal"];
$company->address       = $branchData["direccion"];
$company->phone         = $branchData["telefono"];
$company->whatsapp      = $branchData["telefono"];
$company->email         = $branchData["correo"];
$company->socialReason  = '';
$company->rfc           = $branchData["rfc"];

$seller->number         = /* $data->seller->id */ '--';
$seller->name           = $data->seller->name;
$seller->email          = $data->seller->email;

$customer->company      = $data->customer->name;
$customer->name         = $data->customer->name;
$customer->address      = '';
$customer->email        = $data->customer->email;
$customer->phone        = $data->customer->phone;
$customer->observations = $data->observations ?? '';
$customer->city         = '';

$products->list         = $data->list;
$products->shipment     = 0;
$products->subtotal     = $data->sale_subtotal;
$products->ieps         = $data->sale_ieps ?? 0;
$products->iva          = $data->sale_iva;
$products->total        = $data->sale_total;

$bank->logo             = BASE_URL . '/src/assets/images/bancomer.png';
$bank->accountNumber    = '0196214193';
$bank->clabe            = '0121 0000 1962 1419 36';

$quote->company         = $company;
$quote->seller          = $seller;
$quote->customer        = $customer;
$quote->products        = $products;
$quote->bank            = $bank;
$quote->type            = $data->type;

$quote->folio           = $data->folio;
//$quote->signature       = 'Todos los productos que vendemos están<br>protegidos por nuestro programa de<br>garantía, servicio y refacciones';
$quote->expeditionDate  = $data->date_format;
$quote->expirationDate  = $data->date_expired_format;
$quote->page01Note      = 'NUESTROS REPRESENTANTES DE VENTA NO ESTÁN AUTORIZADOS A RECIBIR PAGOS HECHOS. SOLO ACEPTAMOS ESTOS PAGOS DIRECTAMENTE EN LA CAJA DE NUESTRAS
SUCURSALES Y USTED DEBERÁ RECIBIR UN COMPROBANTE DE RECIBO DE EFECTIVO, MEMBRETADO, FOLIADO, Y FIRMADO POR LA CAJERA Y EL GERENTE DE LA
SUCURSAL; SI USTED EFECTUA SU PAGO A NUESTRO REPRESENTANTE, ESTE DEBERÁ SER CON CHEQUE NOMINATIVO A FAVOR DE GRUPO FINANCIERO PAAL S.A. DE C.V.
CON LA LEYENDA "PARA ABONO EN CUENTA DEL BENEFICIARIO". NO NOS HACEMOS REPONSABLES POR PAGOS QUE NO CUMPLAN CON ESTOS REQUISITOS. LOS PRECIOS
COTIZADOS EN DOLARES O EUROS SE TOMARÁN AL TIPO DE CAMBIO VIGENTE EL DÍA DEL PAGO.';

$quote->page02Note      = 'TIEMPO DE ENTREGA: EL TIEMPO DE ENTREGA DE LOS EQUIPOS SE CONTARÁ A PARTIR DE LA RECEPCIÓN DEL 50 % DE ANTICIPO, ASÍ COMO EL RESPECTIVO PEDIDO
FIRMADO POR EL CLIENTE. EN CASO DE SER UN EQUIPO O MOBILIARIO ESPECIAL, SE DEBERÁ CONTAR ADEMÁS CON LOS DATOS Y ESPECIFICACIONES NECESARIAS
PARA SU FABRICACIÓN. TODA CANCELACIÓN DE EQUIPO CAUSARA UNA PENALIDAD DEL 50 % DEL VALOR TOTA. EN TODOS LOS PEDIDOS DE MOBILIARIO,
COMERCIALIZACIÓN O EQUIPOS DE FABRICACIÓN ESPECIAL, NO SE ACEPTAN CANCELACIONES, DEVOLUCIONES, NI CAMBIOS UNA VEZ FINCADO EL PEDIDO. LOS
PRECIOS COTIZADOS ESTARÁN SUJETOS A CAMBIOS SIN PREVIO AVISO. /SERVICIO Y GARANTIA: TODOS LOS EQUIPOS VENDIDOS CONTARÁN CON UN AÑO DE GARANTIA
A PARTIR DE LA FECHA DE LA FACTURA CUBRIENDO SOLO DEFECTOS DE FABRICACIÓN. TODAS LAS PARTES PARA EL CONTROL Y FLUJO DE CORRIENTE ELÉCTRICA,
GOZARÁN DE UN PERÍODO DE 90 DÍAS. LA GARANTIA NO CUBRE LOS MUEBLES Y EQUIPOS DESCOOMPUESTOS POR MALTRATO EN SU MANEJO O FALTA DE MANTENIMIENTO.
FLETE: EN LOS CASOS EN EL QUE EL FLETE ESTÉ INCLUIDO, ESTÉ SOLO SERÁ DENTRO DEL ÁREA METROPOLITANA Y SE CONSIDERARÁ DE NUESTRAS INSTALACIONES A
LA OBRA A DONDE VAYA A SER ENTREGADO EL EQUIPO. EN CASO QUE LA DIRECCIÓN DE ENTREGA TENGA ALGUNA CARACTERÍSTICA POR LA CUAL SE NECESITAN
MANIOBRAS ESPECIALES ESTAS SERÁN A CARGO Y RESPONSABILIDAD DEL CLIENTE. LAS ENTREGAS SE HARÁN EN UN HORARIO ABIERTO DE 9:30 A LAS 17:00 HORAS.
FLETE POR COBRAR. EL EMBALAJE TENDRÁ UN COSTO EXTRA. LA ENTREGA DE LOS EQUIPOS SE HARÁ A PIE DE CAMIÓN, SIN CONSIDERAR MANIOBRAS A OTROS PISOS.
NO SE REALIZARÁN NINGÚN EMBARQUE, SI EL EQUIPO NO SE ENCUENTRA TOTALMENTE LIQUIDADO. /MONTAJE E INTERCONEXIÓN: MONTAJE Y EMBALAJE DEL EQUIPO
Y/O MOBILIARIO NO ESTÁ INCLUIDO, ESTE DEBERÁ SER COTIZADO CUANDO SE REQUIERE Y CONSIDERA EN: ARMADO EN OBRA DE LOS MUEBLES Y EQUIPOS QUE LO
REQUIERAN, COLGADO DE CAMPANAS, FIJACIÓN DE REPISAS Y MUEBLES A MURO (EN SU CASO), Y NIVELACIÓN DEL EQUIPO, PROPORCIONANDO LA MANO DE OBRA,
MAQUINARIA Y HERRAMIENTAS DE CAMPO NECESARIAS PARA LOS TRABAJOS. COTIZACIÓN VÁLIDA POR 3 DÍAS, LOS PRECIOS COTIZADOS ESTAN SUJETOS A CAMBIOS
SIN PREVIO AVISO. PARA CUALQUIER ACLARACIÓN COMUNIQUESE A LOS TELÉFONOS ARRIBA MENCIONADOS, CON EL AGENTE QUE LE ATENDIÓ O CON EL DEPARTAMENTO
DE ATENCIÓN A CLIENTES.';

$pdf = new QuotePDF($quote);
$pdf->createPDF();
$pdf->showPDF();
