<?php
header('Content-Type: text/html; charset=utf-8');
date_default_timezone_set('America/Mexico_City');

//include( 'inc/security.php' );
include('inc/settings.inc.php');
include("facturacion/FacturacionModerna/FacturacionModerna.php");
require_once('plugins/phpmailer/class.phpmailer.php');
require('fpdf17/fpdf.php');


$arrayFormasPagos = array(
  '01' => 'Efectivo',
  '02' => 'Cheque nominativo',
  '03' => 'Transferencia electrónica de fondos',
  '04' => 'Tarjeta de crédito',
  '05' => 'Monederos electrónicos',
  '06' => 'Dinero electrónico',
  '07' => 'Tarjetas digitales',
  '08' => 'Vales de despensa',
  '09' => 'Bienes',
  '10' => 'Servicio',
  '11' => 'Por cuenta de tercero',
  '12' => 'Dación en pago',
  '13' => 'Pago por subrogación',
  '14' => 'Pago por consignación',
  '15' => 'Condonación',
  '16' => 'Cancelación',
  '17' => 'Compensación',
  '23' => 'Novación',
  '24' => 'Confusión',
  '25' => 'Remisión de deuda',
  '26' => 'Preescripción o caducidad',
  '27' => 'A satisfacción del acreedor',
  '28' => 'Tarjeta de débito',
  '29' => 'Tarjeta de servicios',
  '30' => 'Aplicación de anticipos',
  '98' => 'NA',
  '99' => 'Por definir'
);
$arrayMetodosPagos = array('PUE' => 'Pago en una sola exhibición', 'PPD' => 'Pago en parcialidades o diferido');
$arrayUsoCFDI = array(
  'G01' => 'G01 - Adquisición de mercancias',
  'G02' => 'G02 - Devoluciones, descuentos o bonificaciones',
  'G03' => 'G03 - Gastos en general',
  'I01' => 'I01 - Construcciones',
  'I02' => 'I02 - Mobilario y equipo de oficina por inversiones',
  'I03' => 'I03 - Equipo de transporte',
  'I04' => 'I04 - Equipo de computo y accesorios',
  'I05' => 'I05 - Dados, troqueles, moldes, matrices y herramental',
  'I06' => 'I06 - Comunicaciones telefónicas',
  'I07' => 'I07 - Comunicaciones satelitales',
  'I08' => 'I08 - Otra maquinaria y equipo',
  'D01' => 'D01 - Honorarios médicos, dentales y gastos hospitalarios',
  'D02' => 'D02 - Gastos médicos por incapacidad o discapacidad',
  'D03' => 'D03 - Gastos funerales',
  'D04' => 'D04 - Donativos',
  'D05' => 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios(casa habitación)',
  'D06' => 'D06 - Aportaciones voluntarias al SAR',
  'D07' => 'D07 - Primas por seguros de gastos médicos',
  'D08' => 'D08 - Gastos de transportación escolar obligatoria',
  'D09' => 'D09 - Depósitos en cuentas para el  ahorro, primas que tengan como base planes de pensiones',
  'D10' => 'D10 - Pagos por servicios educativos(colegiaturas)',
  'P01' => 'P01 - Por definir'
);

$arrayObjetoImpuesto = array(
  '01' => 'No objeto de impuesto',
  '02' => 'Sí objeto de impuesto',
  '03' => 'Sí objeto del impuesto y no obligado al desglose'
);

//emisor
$id_emisor           = $_POST['id_emisor'];
$db->Query("SELECT
  id_emisor,
  tipo,
  nombre_razon_social,
  rfc,
  direccion,
  regimen_fiscal,
  archivo_cer,
  archivo_key_pem,
  no_certificado
FROM paal_emisores WHERE id_emisor = '" . $id_emisor . "'");
$dataEmisor = $db->FetchAssoc();


//receptor
$receptorNombre         = $_POST['RazonSocial'];
$receptorRFC            = $_POST['RFC'];
$receptorUsoCFDI        = $_POST['UsoCFDI'];
$domicilioFiscalReceptor = $_POST['DomicilioFiscal'];
$regimenFiscalReceptor  = $_POST['RegimenFiscal'];

$receptorUsoCFDIPDF     = $arrayUsoCFDI[$_POST['UsoCFDI']];
$idCliente              =  isset($_POST['idCliente']) ? $_POST['idCliente'] : '0';


// detalles del pago
$metodoPago         = isset($_POST['MetodoPago']) ? $_POST['MetodoPago'] : 'PUE';
$formaPago          = isset($_POST['FormaPago']) ? $_POST['FormaPago'] : '01';
$formaPDF           = $arrayFormasPagos[$formaPago];
$metodoPDF          = $arrayMetodosPagos[$metodoPago];

//pagos resumen
$subtotal           = isset($_POST['SubTotal']) ? $_POST['SubTotal'] : '0.00';
$descuentoTotal     = isset($_POST['TotalDescuento']) ? $_POST['TotalDescuento'] : '0.00';
$ivaTotal           = isset($_POST['TotalIVA']) ? $_POST['TotalIVA'] : '0.00';
$total              = isset($_POST['Total']) ? $_POST['Total'] : '0.00';

$tipoFactura            = 'I';
$fecha                  = date('Y-m-d', strtotime($_POST['FechaEmision']));
$fechaFacturacion       = date('Y-m-d', strtotime($_POST['FechaEmision'])) . 'T' . date('H:i:s');
$objetoImpuesto         = $_POST['ObjetoImpuesto'];
$objetoImpuestoPDF      = $arrayObjetoImpuesto[$objetoImpuesto];


// obtenemos el folio
$db->Query("SELECT MAX(Folio) as Num FROM facturas");
$dataFlolio = $db->FetchAssoc();
$folioInt = $dataFlolio['Num'] + 1;



/*if(isset($_POST['uidVenta'])){
        $uidVenta = $_POST['uidVenta'];

        $db->Query("SELECT productos.Producto,ventas_detalles.Cantidad, ventas_detalles.SubTotal, ventas_detalles.Total,ventas_detalles.Descuento FROM ventas_detalles LEFT JOIN productos ON(ventas_detalles.idProducto = productos.idProducto) WHERE ventas_detalles.idVenta = '".$uidVenta."'");

      }*/


TimbrarFactura();


/**
 * Prueba de timbrado con la conexion a Facturacion Moderna
 * @return void
 */
function TimbrarFactura()
{
  global $dataEmisor;
  $debug = 1;



  $rfc_emisor         = $dataEmisor['RFC'];
  $numero_certificado = $dataEmisor['NumCertificado'];
  $archivo_cer        = "facturacion/utilerias/certificados/" . $dataEmisor['ArchivoCer'];
  $archivo_pem        = "facturacion/utilerias/certificados/" . $dataEmisor['ArchivoKey'];


  // Datos de acceso al ambiente de producción
  $url_timbrado = "https://t2.facturacionmoderna.com/timbrado/wsdl"; // produccion
  $user_id = "HIMY840518KQ6";
  $user_password = "68958d52976d8075fc79966e9ef5a274509409e5";


  // Datos de acceso al ambiente de pruebas
  /*$url_timbrado = "https://t1demo.facturacionmoderna.com/timbrado/wsdl";
    $user_id = "UsuarioPruebasWS";
    $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";*/

  // generar y sellar un XML con los CSD de pruebas
  $cfdi = generarXML($rfc_emisor);
  $cfdi = sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem);

  // die(var_dump($cfdi));

  $parametros = array('emisorRFC' => $rfc_emisor, 'UserID' => $user_id, 'UserPass' => $user_password);

  $opciones = array();

  /**
   * Establecer el valor a true, si desea que el Web services genere el CBB en
   * formato PNG correspondiente.
   * Nota: Utilizar está opción deshabilita 'generarPDF'
   */
  $opciones['generarCBB'] = true;

  /**
   * Establecer el valor a true, si desea que el Web services genere la
   * representación impresa del XML en formato PDF.
   * Nota: Utilizar está opción deshabilita 'generarCBB'
   */
  $opciones['generarPDF'] = false;

  /**
   * Establecer el valor a true, si desea que el servicio genere un archivo de
   * texto simple con los datos del Nodo: TimbreFiscalDigital
   */
  $opciones['generarTXT'] = false;


  $cliente = new FacturacionModerna($url_timbrado, $parametros, $debug);

  if ($cliente->timbrar($cfdi, $opciones)) {
    // Almacenanos en la raíz del proyecto los archivos generados.
    // carpeta produccción
    $comprobante = 'comprobantes/' . $cliente->UUID;

    // carpeta pruebas
    // $comprobante = 'comprobantes_test/' . $cliente->UUID;

    if ($cliente->xml) {
      //echo "XML almacenado correctamente en $comprobante.xml\n";
      file_put_contents($comprobante . ".xml", $cliente->xml);
    }
    if (isset($cliente->pdf)) {
      //echo "PDF almacenado correctamente en $comprobante.pdf\n";
      file_put_contents($comprobante . ".pdf", $cliente->pdf);
    }
    if (isset($cliente->png)) {
      //echo "CBB en formato PNG almacenado correctamente en $comprobante.png\n";
      file_put_contents($comprobante . ".png", $cliente->png);
    }

    $archivo_PDF = GenerarPDF($comprobante . '.xml', $numero_certificado, $rfc_emisor);
    $status_array = array('message' => 'Facturada generada exitosamente', 'status' => 'true', 'uid' => '0', 'comprobante' => $archivo_PDF);
  } else {

    $res = "[" . $cliente->ultimoCodigoError . "] - " . $cliente->ultimoError . "\n";
    $status_array = array('message' => $res, 'status' => 'false', 'uid' => '0', 'comprobante' => '0');
  }

  echo json_encode($status_array);
}

/**
 * Sellar el comprobante
 * @param  string $cfdi               XML a sellar
 * @param  string $numero_certificado Numero del certificado
 * @param  string $archivo_cer        Ruta del archivo .cer
 * @param  string $archivo_pem        Ruta del archivo .pem
 * @return string                     XML sellado
 */
function sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem)
{
  $private = openssl_pkey_get_private(file_get_contents($archivo_pem));
  $certificado = str_replace(array('\n', '\r'), '', base64_encode(file_get_contents($archivo_cer)));

  $xdoc = new DomDocument();
  $xdoc->loadXML($cfdi) or die("XML invalido");

  $c = $xdoc->getElementsByTagNameNS('http://www.sat.gob.mx/cfd/4', 'Comprobante')->item(0);
  $c->setAttribute('Certificado', $certificado);
  $c->setAttribute('NoCertificado', $numero_certificado);

  $proc = new XSLTProcessor;
  $XSL = new DOMDocument();
  $XSL->load('facturacion/utilerias/xslt40/cadenaoriginal_4_0.xslt');

  $proc->importStyleSheet($XSL);

  $cadena_original = $proc->transformToXML($xdoc);
  openssl_sign($cadena_original, $sig, $private, OPENSSL_ALGO_SHA256);
  $sello = base64_encode($sig);

  $c->setAttribute('Sello', $sello);

  return $xdoc->saveXML();
}


/**
 * Generar el xml basico para el trimbrado
 * @param  string $rfc_emisor RFC del emisor
 * @return string XML valido
 */
function generarXML($rfc_emisor)
{
  global $dataEmisor, $receptorNombre, $receptorRFC, $receptorUsoCFDI, $metodoPago, $formaPago, $subtotal, $descuentoTotal, $ivaTotal, $total, $folioInt, $dataProductos, $db, $domicilioFiscalReceptor, $regimenFiscalReceptor, $fechaFacturacion, $objetoImpuesto;

  //$fecha_actual = substr( date('c'), 0, 19);
  $fecha_actual = $fechaFacturacion;

  $xml = new XMLWriter();
  $xml->openMemory();
  $xml->startDocument('1.0', 'utf-8');
  $xml->setIndent(true);
  /**Inicio nodo Comprobantes*/
  $xml->startElementNs('cfdi', 'Comprobante', null);
  $xml->writeAttributeNS('xmlns', 'cfdi', null, 'http://www.sat.gob.mx/cfd/4');
  $xml->writeAttributeNS('xmlns', 'xs', null, 'http://www.w3.org/2001/XMLSchema');
  $xml->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
  $xml->writeAttributeNS('xsi', 'schemaLocation', null, 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd');

  /*Atributos nodo Comprobantes*/
  $xml->writeAttribute('Version', '4.0');
  $xml->writeAttribute('Fecha', $fecha_actual);
  $xml->writeAttribute('Folio', $folioInt);/*Revisar*/
  //$xml->writeAttribute('Serie','');/*Revisar si los folios tienen serie*/
  $xml->writeAttribute('FormaPago', $formaPago);  /**/
  $xml->writeAttribute('MetodoPago', $metodoPago);/*Revisar*/
  /*$xml->writeAttribute('CondicionesDePago','Contado');      ----->          opcional*/

  $xml->writeAttribute('Moneda', 'MXN');
  $xml->writeAttribute('TipoCambio', '1');
  $xml->writeAttribute('SubTotal', $subtotal);/*Revisar*/
  $xml->writeAttribute('Descuento', $descuentoTotal);
  $xml->writeAttribute('Total', $total);/*Revisar*/
  $xml->writeAttribute('TipoDeComprobante', 'I'); //en la versión anterior era la palabra completa "Ingreso"
  $xml->writeAttribute('LugarExpedicion', '30000');
  $xml->writeAttribute('Exportacion', '01');
  //$xml->writeAttribute('NoCertificado',$no_certificado);  /*se puede obtener de la bd*/
  //$xml->writeAttribute('NumCtaPago','NO IDENTIFICADO');

  if (isset($_POST['UUIDS']) && $_POST['TipoRelacion'] != '' and $_POST['UUIDS'] != '') {
    $UuidsRelacionados = explode(',', $_POST['UUIDS']);
    /*Inicio nodo relaciones de facturas relacionadas cuando la venta es a crédito*/
    $xml->startElementNs('cfdi', 'CfdiRelacionados', null);
    $xml->writeAttribute('TipoRelacion', $_POST['TipoRelacion']);
    for ($i = 0; $i < count($UuidsRelacionados); $i++) {
      $xml->startElementNs('cfdi', 'CfdiRelacionado', null);
      $xml->writeAttribute('UUID', $UuidsRelacionados[$i]);
      $xml->endElement();
    }
    $xml->endElement();
    /*Fin nodo relaciones*/
  }

  /*Inicio nodo Emisor*/
  $xml->startElementNs('cfdi', 'Emisor', null);
  $xml->writeAttribute('Rfc', $dataEmisor['RFC']); //$rfcE
  $xml->writeAttribute('Nombre', $dataEmisor['Nombre']); //$emisor['Nombre']
  $xml->writeAttribute('RegimenFiscal',  $dataEmisor['RegimenFiscalClave']); //$emisor['RegimenFiscal']
  $xml->endElement();
  /*Fin nodo Emisor*/

  /*Inicio nodo Receptor*/
  $xml->startElementNs('cfdi', 'Receptor', null);
  $xml->writeAttribute('Rfc', $receptorRFC); // $cliente['RFC']
  $xml->writeAttribute('Nombre', $receptorNombre); //$cliente['Cliente']
  $xml->writeAttribute('UsoCFDI', $receptorUsoCFDI);/*GASTOS EN GENERAL*/
  $xml->writeAttribute('DomicilioFiscalReceptor', $domicilioFiscalReceptor);
  $xml->writeAttribute('RegimenFiscalReceptor', $regimenFiscalReceptor);
  $xml->endElement();
  /*Fin nodo receptor*/

  /*Inicio nodo Conceptos */
  $xml->startElementNs('cfdi', 'Conceptos', null);

  if (isset($_POST['uidVenta']) and $_POST['uidVenta'] != '') {

    while ($dataProductos = $db->FetchArray()) {
      $xml->startElementNs('cfdi', 'Concepto', null);
      $claveProdServ = ($dataProductos['ClaveProdServ'] == '') ? '31231400' : $dataProductos['ClaveUnidad'];
      $claveUnidad = ($dataProductos['ClaveUnidad'] == '') ? 'H87' : $dataProductos['ClaveUnidad'];
      $xml->writeAttribute('Cantidad', $dataProductos['Cantidad']);
      $xml->writeAttribute('ClaveUnidad', $claveUnidad);
      $xml->writeAttribute('ClaveProdServ', $claveProdServ);
      $xml->writeAttribute('Descripcion', $dataProductos['Producto']);/*Revisar*/
      $xml->writeAttribute('ValorUnitario', $dataProductos['SubTotal']);/*Revisar*/
      $xml->writeAttribute('Importe', $dataProductos['Total']);/*Revisar*/
      $xml->writeAttribute('Descuento', $dataProductos['Descuento']);/*Revisar*/
      $xml->writeAttribute('ObjetoImp', '02');/*Revisar esto está estático y la configuracion tendria que ser por producto en la tabla de productos*/

      if ($_POST['IVA'][$i] > 0) {
        $xml->startElementNs('cfdi', 'Impuestos', null);
        $xml->startElementNs('cfdi', 'Traslados', null);
        $xml->startElementNs('cfdi', 'Traslado', null);
        $xml->writeAttribute('Base', $_POST['Importe'][$i]);
        $xml->writeAttribute('Impuesto', '002');
        $xml->writeAttribute('TipoFactor', 'Tasa');
        $xml->writeAttribute('TasaOCuota', '0.160000');
        $xml->writeAttribute('Importe', $_POST['IVA'][$i]);
        $xml->endElement();
        $xml->endElement();

        $xml->endElement();
        //finaliza impuestos
      }

      $xml->endElement();
      //finaliza concepto
    }
  } else {

    for ($i = 0; $i < count($_POST['Cantidad']); $i++) {
      $xml->startElementNs('cfdi', 'Concepto', null);
      $xml->writeAttribute('ClaveProdServ', $_POST['ClaveProdServ'][$i]);
      $xml->writeAttribute('Cantidad', $_POST['Cantidad'][$i]);
      $xml->writeAttribute('ClaveUnidad', $_POST['ClaveUnidad'][$i]);
      $xml->writeAttribute('Descripcion', $_POST['Concepto'][$i]);/*Revisar*/
      $xml->writeAttribute('ValorUnitario', $_POST['PrecioUnitario'][$i]);/*Revisar*/
      $xml->writeAttribute('Descuento', $_POST['Descuento'][$i]);/*Revisar*/
      $xml->writeAttribute('Importe', $_POST['Importe'][$i]);/*Revisar*/
      $xml->writeAttribute('ObjetoImp', $objetoImpuesto);/*Revisar*/


      if ($_POST['IVA'][$i] > 0) {
        $xml->startElementNs('cfdi', 'Impuestos', null);
        $xml->startElementNs('cfdi', 'Traslados', null);
        $xml->startElementNs('cfdi', 'Traslado', null);
        $xml->writeAttribute('Base', $_POST['Importe'][$i]);
        $xml->writeAttribute('Impuesto', '002');
        $xml->writeAttribute('TipoFactor', 'Tasa');
        $xml->writeAttribute('TasaOCuota', '0.160000');
        $xml->writeAttribute('Importe', $_POST['IVA'][$i]);
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
        //finaliza impuestos
      }

      $xml->endElement();
      //finaliza concepto
    }
  }

  $xml->endElement();
  /*Fin nodo Conceptos */
  if ($ivaTotal > 0) {
    /*Inicio nodo Impuestos*/
    $xml->startElementNs('cfdi', 'Impuestos', null);
    $xml->writeAttribute('TotalImpuestosTrasladados', $ivaTotal);
    $xml->startElementNs('cfdi', 'Traslados', null);
    $xml->startElementNs('cfdi', 'Traslado', null);
    $xml->writeAttribute('Impuesto', '002');
    $xml->writeAttribute('TipoFactor', 'Tasa');
    $xml->writeAttribute('TasaOCuota', '0.160000');
    $xml->writeAttribute('Importe', $ivaTotal);
    $xml->writeAttribute('Base', $subtotal);
    $xml->endElement();
    $xml->endElement();
    $xml->endElement();
    /*Fin nodo Impuestos*/
  }



  /*if($ishTotal > 0){
            $xml->startElementNs('cfdi', 'Complemento', null);
                $xml->startElementNs('implocal', 'ImpuestosLocales', null);
                    $xml->writeAttributeNS('xmlns','implocal', null, 'http://www.sat.gob.mx/implocal');
                    $xml->writeAttribute('version', '1.0' );
                    $xml->writeAttribute('TotaldeRetenciones','0.00' );
                    $xml->writeAttribute('TotaldeTraslados', $ishTotal);
                    $xml->startElementNs('implocal', 'TrasladosLocales', null);
                        $xml->writeAttribute('ImpLocTrasladado', 'ISH');
                        $xml->writeAttribute('TasadeTraslado', '2.00');
                        $xml->writeAttribute('Importe', $ishTotal);
                    $xml->endElement();

                $xml->endElement();
            $xml->endElement();
        }*/


  $xml->endElement();
  /*Fin nodo comprobante*/
  $prexML = $xml->outputMemory(true);
  $cfdi = $prexML;
  return $cfdi;
}

function GenerarPDF($comprobante, $numero_certificado, $rfc_emisor)
{
  global $folioInt, $total, $formaPDF, $metodoPDF, $descuentoTotal,  $subtotal, $ivaTotal, $idCliente, $tipoFactura, $db, $dataProductos,
    $receptorNombre,
    $receptorRFC,
    $receptorUsoCFDIPDF,
    $dataEmisor,
    $fecha,
    $objetoImpuestoPDF;




  class PDF extends FPDF
  {

    var $widths;
    var $aligns;
    function SetWidths($w)
    {
      $this->widths = $w;
    }
    function SetAligns($a)
    {
      $this->aligns = $a;
    }
    function fill($f)
    {
      $this->fill = $f;
    }
    function Row($data, $fill = false, $style = "DF")
    {
      $nb = 0;
      for ($i = 0; $i < count($data); $i++)
        $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
      $h = 5 * $nb;
      $this->CheckPageBreak($h);
      for ($i = 0; $i < count($data); $i++) {
        $w = $this->widths[$i];
        $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Rect($x, $y, $w, $h, $style);
        $this->MultiCell($w, 5, $data[$i], 'LTR', $a, $fill);
        $this->SetXY($x + $w, $y);
      }
      $this->Ln($h);
    }
    function CheckPageBreak($h)
    {
      if ($this->GetY() + $h > $this->PageBreakTrigger) {
        $this->AddPage($this->CurOrientation);
      }
    }

    function NbLines($w, $txt)
    {
      $cw = &$this->CurrentFont['cw'];
      if ($w == 0)
        $w = $this->w - $this->rMargin - $this->x;
      $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
      $s = str_replace('\r', '', $txt);
      $nb = strlen($s);
      if ($nb > 0 and $s[$nb - 1] == '\n')
        $nb–;
      $sep = -1;
      $i = 0;
      $j = 0;
      $l = 0;
      $nl = 1;
      while ($i < $nb) {
        $c = $s[$i];
        if ($c == '\n') {
          $i++;
          $sep = -1;
          $j = $i;
          $l = 0;
          $nl++;
          continue;
        }
        if ($c == ' ')
          $sep = $i;
        $l += $cw[$c];
        if ($l > $wmax) {
          if ($sep == -1) {
            if ($i == $j)
              $i++;
          } else {
            $i = $sep + 1;
          }
          $sep = -1;
          $j = $i;
          $l = 0;
          $nl++;
        } else {
          $i++;
        }
      }
      return $nl; //*/
    }
    //fin del código de autoajustar
    function Header()
    {

      global $dataEmisor;
      $this->SetFont('Arial', 'B', 8);
      $this->Cell(20);
      $this->Image('img/logop.png', 10, 5, 65, 25, 'PNG');
      $this->Ln(30);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['Nombre']), 0, 0, '');
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['RFC']), 0, 0);
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode("RÉGIMEN FISCAL: " . $dataEmisor['RegimenFiscalText'] . ""), 0, 0);
      $this->Ln(4);
      $this->SetFont('Arial', '', 8);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['Calle']), 0, 0);
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['Ciudad'] . ' CP ' . $dataEmisor['CP']), 0, 0);
    }
    function Footer()
    {
      $this->SetY(-5);
      $this->SetFont('Arial', 'I', 8);
      $this->Cell(0, 0, utf8_decode("provisionescomitan@gmail.com | 963 636 8037 | 963 123 9192 | provisionescomitan.com"), 0, 0, 'R', true);
    }
  }


  /*///////////////////////////
    /////// Creación del //////
    ///////     PDF   //////////
    //////////////////////////*/
  // Creación del objeto de la clase heredada
  $xml = simplexml_load_file($comprobante);
  $ns = $xml->getNamespaces(true);
  $xml->registerXPathNamespace('c', $ns['cfdi']);
  $xml->registerXPathNamespace('t', $ns['tfd']);
  //QR///

  //$Repositorio = new Repositorio();
  foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
    $UUID = $tfd['UUID'];
    $CFD = $tfd['SelloCFD'];
    $SAT = $tfd['SelloSAT'];
    $version = $tfd['Version'];
    $fechaTimbrado = $tfd['FechaTimbrado'];
    $certificadoSAT = $tfd['NoCertificadoSAT'];
  }



  $pdf = new PDF();
  $pdf->AliasNbPages();
  $pdf->AddPage();
  $pdf->SetDrawColor(224, 224, 224);
  //agregando el FOLIO interno
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetXY(140, 4);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "FOLIO", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $folioInt, 1, 0, "L");
  $pdf->Ln(5);
  //fin del codigo para agregar el FOLIO interno

  /*$pdf->SetFont('Arial','',8);
    $pdf->SetX(140);
    $pdf->SetTextColor(0,0,0);
    $pdf->SetFillColor(224,224,224);
    $pdf->Cell(60, 5,"RECIBO",1,0,"L",true);
    $pdf->Ln(5);
    $pdf->SetFont('Arial','',7);
    $pdf->SetX(140);
    $pdf->SetTextColor(0,0,0);
    $pdf->Cell(60, 5,'seriado',1,0,"L");
    $pdf->Ln(5);*/
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "FOLIO FISCAL", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $UUID, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX(140);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "NO. DE SERIE DE CERTIFICADO DEL SAT", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $certificadoSAT, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX(140);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "NO. DE SERIE DE CERTIFICADO DEL CSD", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $dataEmisor['NumCertificado'], 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX(140);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "FECHA DE CERTIFICACION", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $fechaTimbrado, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX(140);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "LUGAR Y FECHA DE ELABORACION", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(60, 5,utf8_decode($emisor['Municipio']).", ".utf8_decode($emisor['Estado']).", ".date('Y-m-d'),1,0,"L");
  $pdf->Cell(60, 5, utf8_decode('Comitán de Domínguez') . ", " . utf8_decode('Chiapas') . ", " . $fecha, 1, 0, "L");
  $pdf->Ln(30);
  $pdf->SetY(70);
  $pdf->SetFont('Arial', '', 10);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(85, 5, "RECEPTOR", 0, 0, "L", true);
  $pdf->Ln();
  $pdf->SetFont('Arial', '', 8);
  $pdf->MultiCell(110, 5, utf8_decode($receptorNombre) . "\n" . utf8_decode($receptorRFC) . "\n" . utf8_decode('Uso del CFDI: ' . $receptorUsoCFDIPDF), 0, "L");
  $pdf->SetXY(0, 65);
  $pdf->Ln(20);


  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFillColor(0, 0, 0);
  $pdf->Cell(10, 5, "CANT", 1, 0, "C", true);
  $pdf->Cell(20, 5, "UNIDAD", 1, 0, "C", true);
  $pdf->Cell(25, 5, "PRD/SV", 1, 0, "C", true);
  $pdf->Cell(80, 5, utf8_decode("DESCRIPCIÓN"), 1, 0, "C", true);
  $pdf->Cell(30, 5, "PRECIO UNIT.", 1, 0, "C", true);
  //$pdf->Cell(25,5,"DESC.",1,0,"C",true);
  $pdf->Cell(25, 5, "IMPORTE", 1, 0, "C", true);
  $pdf->Ln(5);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(10,5,"1.0",1,0,"L");

  /** lista las lineas de articulos  **/
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetWidths(array(10, 20, 25, 80, 30, 25));
  $pdf->SetAligns(array('L', 'L', 'L', 'L', 'R', 'R', 'R'));

  if (isset($_POST['uidVenta']) and $_POST['uidVenta'] != '') {

    while ($dataProductos = $db->FetchArray()) {

      $claveProdServ = ($dataProductos['ClaveProdServ'] == '') ? '31231400' : $dataProductos['ClaveUnidad'];
      $claveUnidad = ($dataProductos['ClaveUnidad'] == '') ? 'H87' : $dataProductos['ClaveUnidad'];

      $pdf->Row(array(
        utf8_decode($dataProductos['Cantidad']),
        utf8_decode($claveUnidad),
        utf8_decode($claveProdServ),
        utf8_decode($dataProductos['Producto']),
        utf8_decode($dataProductos['SubTotal']),
        //utf8_decode($dataProductos['Descuento']),
        utf8_decode($dataProductos['Total']),
      ));
    }
  } else {
    for ($i = 0; $i < count($_POST['Cantidad']); $i++) {
      $pdf->Row(array(
        utf8_decode($_POST['Cantidad'][$i]),
        utf8_decode($_POST['ClaveUnidad'][$i]),
        utf8_decode($_POST['ClaveProdServ'][$i]),
        utf8_decode($_POST['Concepto'][$i]),
        utf8_decode($_POST['PrecioUnitario'][$i]),
        //utf8_decode($_POST['Descuento'][$i]),
        utf8_decode($_POST['Importe'][$i]),
      ));
    }
  }


  /** Finaliza lista de articulos **/
  /* for($h=1;$h<=1;$h++)
    {
        $pdf->Cell(30,5,"",1,0,"L");
        $pdf->Cell(10,5,"",1,0,"L");
        $pdf->Cell(25,5,"",1,0,"C");
        $pdf->Cell(75,5,"",1,0,"L");
        $pdf->Cell(25,5,"",1,0,"R");
        $pdf->Cell(25,5,"",1,0,"R");
        $pdf->Ln(5);
    }*/
  $pdf->Cell(140, 5, "Cantidad con letra:  " . numtoletras($total), 0, 0, "L");

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "Subtotal:", 1, 0, "R");
  $pdf->Cell(25, 5, number_format($subtotal, 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);

  $pdf->Cell(140, 5, "Forma de Pago: " . utf8_decode($formaPDF), 0, 0, "L");

  /* $pdf->SetX(145);
    $pdf->Cell(30,5,utf8_decode("ISH 2.00%"),1,0,"R");
    $pdf->Cell(25,5,number_format($ishTotal,2,".",","),1,0,"R");
    $pdf->Ln(5); */

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "IVA 16.00%", 1, 0, "R");
  $pdf->Cell(25, 5, number_format($ivaTotal, 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);


  $pdf->Cell(140, 5, utf8_decode("Método de Pago: " . $metodoPDF), 0, 0, "L");

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "TOTAL:", 1, 0, "R");
  $pdf->Cell(25, 5, number_format($total, 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);


  $pdf->Cell(140, 5, utf8_decode("Objeto de impuesto: " . $objetoImpuestoPDF), 0, 0, "L");


  $pdf->SetY(215);
  $pdf->Cell(0, 0, "Este documento es una representacion impresa de un CFDI", 0, 0, "L");
  $pdf->Ln(11);

  $pdf->Image('comprobantes/' . $UUID . '.png', 10, null, 30, 30, 'PNG');

  $pdf->SetFont('Arial', '', 5);
  $pdf->SetXY(43, 220);
  $pdf->MultiCell(55, 5, utf8_decode("Sello Timbre") . "\n||" . $version . "|" . $UUID . "|" . $fechaTimbrado . "|" . $CFD . "|" . $certificadoSAT, 1, 'L');

  $pdf->SetXY(100, 220);
  $pdf->MultiCell(53, 5, "Sello del SAT\n" . $SAT, 1, "L");

  $pdf->SetXY(155, 220);
  $pdf->MultiCell(45, 5, "Sello Digital\n" . $CFD, 1, "L");

  $archivo_PDF = "comprobantes/" . $UUID . ".pdf";
  $pdf->Output($archivo_PDF, 'F');


  $db->Query("INSERT INTO facturas (Fecha,Folio,UUID,PDF,XML,idCliente,Cliente,Emisor,id_emisor,EmisorRFC,Importe,TipoFactura)
                            VALUES (NOW(),'" . $folioInt . "','" . $UUID . "','" . $archivo_PDF . "','" . $comprobante . "','" . $idCliente . "','" . $receptorNombre . "','" . $dataEmisor['Nombre'] . "','" . $dataEmisor['id_emisor'] . "','" . $dataEmisor['RFC'] . "','" . $total . "','I')");

  $idFactura = $db->InsertID();

  if (isset($_POST['EnviarFactura']) and  $_POST['EnviarFactura'] == 'Si' and  trim($_POST['CorreosFCT']) != '') {

    $mail = new PHPMailer();
    try {
      $body .= "<h4>PROVISIONES COMITÁN</h4>";
      $body .= "PRESENTE<br /><br />";
      $body .= "Por medio de la presente le informamos que PROVISIONES COMITÁN le ha enviado un nuevo Comprobante Fiscal Digital.<br /><br /><br />";
      $body .= "<strong>Atentamente</strong><br />";
      $body .= "PROVISIONES COMITÁN<br />";
      $body .= "facturacion@provisionescomitan.com<br />";
      $mail->SetFrom('facturacion@provisionescomitan.com', 'PROVISIONES COMITÁN');
      $address = $_POST['CorreosFCT'];
      $mail->AddAddress($address, utf8_decode($_POST['RazonSocial']));
      $mail->Subject = 'Comprobante Fiscal Digital ' . $_POST['RFC'];
      $mail->AltBody = 'Mensaje alternativo';
      $mail->MsgHTML(utf8_decode($body));
      $mail->addAttachment($comprobante);
      $mail->addAttachment($archivo_PDF);
      if ($mail->Send()) {

        $db->Query("UPDATE facturas SET Enviado = 'Si' WHERE uid = '" . $idFactura . "'");
        //echo "<script>alert('Factura enviada correctamente!'); window.opener.location.reload(); window.close();</script>";

      } else {
        //echo "<script>alert('Ha ocurrido un error al intentar enviar!. Verifica que el cliente tenga registrado su correo electrónico');  window.close();</script>";
      }
    } catch (phpmailerException $e) {
      echo $e->errorMessage();
    }
  }

  //insertamos el nuevo folio consumido
  //$db->Exec("INSERT INTO facturacion_folios(Folio,Fecha,idUserCaptura) VALUES ('".$folioInt."',NOW(),'".$_SESSION["Med_idUsuario"]."')");


  return $archivo_PDF;
}
