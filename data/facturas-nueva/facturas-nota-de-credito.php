<?php
require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

const FCS_CARPETA_URL   = CARPETA_FACTURAS_NOTA_DE_CREDITO_URL;
const FCS_CARPETA_PATH  = CARPETA_FACTURAS_NOTA_DE_CREDITO_PATH;

$result     = mysqli_query($mysqli, "SELECT MAX(folio) as Num FROM paal_facturas_nota_credito WHERE serie = '{$folioSerie}'");
$dataFlolio = mysqli_fetch_assoc($result);
$folioInt   = $dataFlolio['Num'] + 1;
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

  if (!$numero_certificado) :
    echo json_encode([]);
    die;
  endif;

  $archivo_cer        = FACTURAS_CERTIFICADO_PATH . $dataEmisor['ArchivoCer'];
  $archivo_pem        = FACTURAS_CERTIFICADO_PATH . $dataEmisor['ArchivoKey'];

  $existArchivoCer = file_exists($archivo_cer);
  $existArchivoPem = file_exists($archivo_pem);

  if (!$existArchivoCer) :
    echo json_encode([]);
    die;
  endif;

  if (!$existArchivoPem) :
    echo json_encode([]);
    die;
  endif;


  // Datos de acceso al ambiente de producción
  // $url_timbrado = "https://t2.facturacionmoderna.com/timbrado/wsdl"; // produccion
  // $user_id = "HIMY840518KQ6";
  // $user_password = "68958d52976d8075fc79966e9ef5a274509409e5";


  // Datos de acceso al ambiente de pruebas
  $url_timbrado   = FACTURAS_URL_TIMBRADO;
  $user_id        = FACTURAS_ID_USUARIO;
  $user_password  = FACTURAS_PASSWORD;

  // generar y sellar un XML con los CSD de pruebas
  $cfdi = generarXML($rfc_emisor);
  $cfdi = sellarXML($cfdi, $numero_certificado, $archivo_cer, $archivo_pem);


  /* $response["alertMessage"] = "$cfdi, $numero_certificado, $archivo_cer, $archivo_pem" . json_encode($cfdi);
      echo json_encode($response);
      die; */

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
    $comprobante = FCS_CARPETA_PATH . $cliente->UUID;
    $nombreComprobante = $cliente->UUID . '.xml';
    $ubicacionComprobante = FCS_CARPETA_URL . $cliente->UUID . ".pdf";

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

    $archivo_PDF = GenerarPDF($comprobante . '.xml', $numero_certificado, $rfc_emisor, $nombreComprobante);
    $status_array = array('alertMessage' => 'Facturada generada exitosamente', "title" => "¡Success!", 'status' => 'success', 'uid' => '0', 'comprobante' => $archivo_PDF, "callback" => "window.open('" . $ubicacionComprobante . "', '_blank'); location.reload();");
  } else {

    $res = "[" . $cliente->ultimoCodigoError . "] - " . $cliente->ultimoError . "\n";
    $status_array = array('alertMessage' => $res, 'status' => 'error', 'uid' => '0', 'comprobante' => '0');
  }

  echo json_encode($status_array);
  die;
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

  $file = FACTURAS_SELLO_XSLT40_PATH;

  $fileExists = file_exists($file);

  if (!$fileExists) :
    $response["toastMessage"] = "¡No se ha encontrado el archivo!";
    echo json_encode($response);
    die;
  endif;

  $proc = new XSLTProcessor;
  $XSL = new DOMDocument();
  $XSL->load($file);

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

  global $tipoFactura;
  global $folioSerie;
  global $dataSucursal;

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
  $xml->writeAttribute('Serie', $folioSerie);/*Revisar si los folios tienen serie*/
  $xml->writeAttribute('FormaPago', $formaPago);  /**/
  $xml->writeAttribute('MetodoPago', $metodoPago);/*Revisar*/
  /*$xml->writeAttribute('CondicionesDePago','Contado');      ----->          opcional*/

  $xml->writeAttribute('Moneda', 'MXN');
  $xml->writeAttribute('TipoCambio', '1');
  $xml->writeAttribute('SubTotal', number_format((float) $subtotal, 2, '.', ''));
  $xml->writeAttribute('Descuento', number_format((float) $descuentoTotal, 2, '.', ''));
  $xml->writeAttribute('Total', number_format((float) $total, 2, '.', ''));
  $xml->writeAttribute('TipoDeComprobante', $tipoFactura); //en la versión anterior era la palabra completa "Ingreso"
  $xml->writeAttribute('LugarExpedicion', $dataSucursal["cp"]);
  $xml->writeAttribute('Exportacion', '01');
  //$xml->writeAttribute('NoCertificado',$no_certificado);  /*se puede obtener de la bd*/
  //$xml->writeAttribute('NumCtaPago','NO IDENTIFICADO');

  /* if (isset($_POST['UUIDS']) && $_POST['TipoRelacion'] != '' and $_POST['UUIDS'] != '') {
    $UuidsRelacionados = explode(',', $_POST['UUIDS']);
    $xml->startElementNs('cfdi', 'CfdiRelacionados', null);
    $xml->writeAttribute('TipoRelacion', $_POST['TipoRelacion']);
    for ($i = 0; $i < count($UuidsRelacionados); $i++) {
      $xml->startElementNs('cfdi', 'CfdiRelacionado', null);
      $xml->writeAttribute('UUID', $UuidsRelacionados[$i]);
      $xml->endElement();
    }
    $xml->endElement();
  } */

  /**
   * Inicio Nodos relacionados
   */
  $tipoRelacion     = $_POST["tipo_relacion"];
  $cfdiRelacionado  = $_POST["cfdi_relacionado"] ?? [];

  if ($tipoRelacion != "" && count($cfdiRelacionado) > 0) {
    $xml->startElementNs('cfdi', 'CfdiRelacionados', null);

    $xml->writeAttribute('TipoRelacion', $tipoRelacion);

    foreach ($cfdiRelacionado as $cfdi) {
      $xml->startElementNs('cfdi', 'CfdiRelacionado', null);
      $xml->writeAttribute('UUID', $cfdi);
      $xml->endElement();
    }

    $xml->endElement();
  }
  /**
   * Fin Nodos relacionados
   */

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

  $formatMoney2 = function ($value) {
    return number_format((float) $value, 2, '.', '');
  };

  $formatSatDecimals = function ($value) {
    return number_format((float) $value, DECIMALS_CURRENCY, '.', '');
  };

  $trasladoResumen = [];
  $addTrasladoResumen = function ($impuesto, $rate, $base, $importe) use (&$trasladoResumen) {
    $importe = doubleval($importe);
    if ($importe <= 0) return;

    $rate = doubleval($rate);
    $base = doubleval($base);
    $rateFormatted = number_format($rate, 6, '.', '');
    $key = "{$impuesto}|{$rateFormatted}";

    if (!isset($trasladoResumen[$key])) {
      $trasladoResumen[$key] = [
        'impuesto' => $impuesto,
        'rate'     => $rateFormatted,
        'base'     => 0,
        'importe'  => 0
      ];
    }

    $trasladoResumen[$key]['base'] += $base;
    $trasladoResumen[$key]['importe'] += $importe;
  };

  if (isset($_POST['uidVenta']) and $_POST['uidVenta'] != '') {

    while ($dataProductos = $db->FetchArray()) {
      $xml->startElementNs('cfdi', 'Concepto', null);
      $claveProdServ = ($dataProductos['ClaveProdServ'] == '') ? '31231400' : $dataProductos['ClaveUnidad'];
      $claveUnidad = ($dataProductos['ClaveUnidad'] == '') ? 'H87' : $dataProductos['ClaveUnidad'];
      $importe = doubleval($dataProductos['Total'] ?? 0);
      $descuento = doubleval($dataProductos['Descuento'] ?? 0);
      $baseSinImpuestos = max(0, $importe - $descuento);
      $ieps = doubleval($dataProductos['iepsCurrency'] ?? $dataProductos['ieps'] ?? 0);
      $iva = doubleval($dataProductos['ivaCurrency'] ?? $dataProductos['IVA'] ?? 0);
      $iepsRate = doubleval($dataProductos['iepsPercentage'] ?? $dataProductos['ieps_porcentaje'] ?? 0) / 100;
      if ($ieps > 0 && $iepsRate <= 0 && $baseSinImpuestos > 0) $iepsRate = $ieps / $baseSinImpuestos;
      $ivaRate = ($iva > 0) ? 0.16 : 0;
      $baseIVA = $baseSinImpuestos + $ieps;
      $objetoImp = ($ieps > 0 || $iva > 0) ? '02' : '01';

      $xml->writeAttribute('Cantidad', $dataProductos['Cantidad']);
      $xml->writeAttribute('ClaveUnidad', $claveUnidad);
      $xml->writeAttribute('ClaveProdServ', $claveProdServ);
      $xml->writeAttribute('Descripcion', $dataProductos['Producto']);/*Revisar*/
      $xml->writeAttribute('ValorUnitario', $formatSatDecimals($dataProductos['SubTotal'] ?? 0));
      $xml->writeAttribute('Importe', $formatSatDecimals($dataProductos['Total'] ?? 0));
      $xml->writeAttribute('Descuento', $formatSatDecimals($dataProductos['Descuento'] ?? 0));
      $xml->writeAttribute('ObjetoImp', $objetoImp);

      if ($ieps > 0 || $iva > 0) {
        $xml->startElementNs('cfdi', 'Impuestos', null);
        $xml->startElementNs('cfdi', 'Traslados', null);

        if ($ieps > 0) {
          $xml->startElementNs('cfdi', 'Traslado', null);
          $xml->writeAttribute('Base', $formatSatDecimals($baseSinImpuestos));
          $xml->writeAttribute('Impuesto', '003');
          $xml->writeAttribute('TipoFactor', 'Tasa');
          $xml->writeAttribute('TasaOCuota', number_format($iepsRate, 6, '.', ''));
          $xml->writeAttribute('Importe', $formatSatDecimals($ieps));
          $xml->endElement();

          $addTrasladoResumen('003', $iepsRate, $baseSinImpuestos, $ieps);
        }

        if ($iva > 0) {
          $xml->startElementNs('cfdi', 'Traslado', null);
          $xml->writeAttribute('Base', $formatSatDecimals($baseIVA));
          $xml->writeAttribute('Impuesto', '002');
          $xml->writeAttribute('TipoFactor', 'Tasa');
          $xml->writeAttribute('TasaOCuota', number_format($ivaRate, 6, '.', ''));
          $xml->writeAttribute('Importe', $formatSatDecimals($iva));
          $xml->endElement();

          $addTrasladoResumen('002', $ivaRate, $baseIVA, $iva);
        }

        $xml->endElement();
        $xml->endElement();
      }

      $xml->endElement();
    }
  } else {
    global $_POST;
    global $facturacionHelpers;
    global $arrayObjetoImpuesto;

    $productos = $_POST['productos'];
    $productos = json_decode($productos, true);

    foreach ($productos as $producto) :
      $claveProdServ  = $producto['keyProductServiceId'] !== "84111506" ? $facturacionHelpers->getkeyProductoServicio($producto['keyProductServiceId']) : $producto['keyProductServiceId'];
      $cantidad       = $producto['quantity'];
      $claveUnidad    = $producto['unitMeasurementId'] !== "ACT" ? $facturacionHelpers->getkeyUnidad($producto['unitMeasurementId']) : $producto['unitMeasurementId'];
      $descripcion    = $producto['productName'];
      $valorUnitario  = $producto['priceWithoutIVA'];
      $descuento      = $producto["discount"];
      $importe        = $producto['amountWithoutIVA'];
      $ieps           = doubleval($producto['iepsCurrency'] ?? 0);
      $iepsRate       = doubleval($producto['iepsPercentage'] ?? 0) / 100;
      $iva            = $producto['ivaCurrency'];
      $ivaRate        = ($iva > 0) ? doubleval($producto['iva'] ?? 16) / 100 : 0;
      $baseSinImpuestos = max(0, doubleval($importe) - doubleval($descuento));
      $baseIVA = $baseSinImpuestos + $ieps;

      $objetoImp      = $producto['taxObject'];
      //$objetoImp      = $arrayObjetoImpuesto[$objetoImpuesto];

      $xml->startElementNs('cfdi', 'Concepto', null);
      $xml->writeAttribute('ClaveProdServ', $claveProdServ);
      $xml->writeAttribute('Cantidad', $cantidad);
      $xml->writeAttribute('ClaveUnidad', $claveUnidad);
      $xml->writeAttribute('Descripcion', $descripcion);
      $xml->writeAttribute('ValorUnitario', $formatSatDecimals($valorUnitario));
      $xml->writeAttribute('Descuento', $formatSatDecimals($descuento));
      $xml->writeAttribute('Importe', $formatSatDecimals($importe));
      $xml->writeAttribute('ObjetoImp', $objetoImp);

      if ($ieps > 0 || $iva > 0) :
        $xml->startElementNs('cfdi', 'Impuestos', null);
        $xml->startElementNs('cfdi', 'Traslados', null);

        if ($ieps > 0) {
          $xml->startElementNs('cfdi', 'Traslado', null);
          $xml->writeAttribute('Base', $formatSatDecimals($baseSinImpuestos));
          $xml->writeAttribute('Impuesto', '003');
          $xml->writeAttribute('TipoFactor', 'Tasa');
          $xml->writeAttribute('TasaOCuota', number_format($iepsRate, 6, '.', ''));
          $xml->writeAttribute('Importe', $formatSatDecimals($ieps));
          $xml->endElement();

          $addTrasladoResumen('003', $iepsRate, $baseSinImpuestos, $ieps);
        }

        if ($iva > 0) {
          $xml->startElementNs('cfdi', 'Traslado', null);
          $xml->writeAttribute('Base', $formatSatDecimals($baseIVA));
          $xml->writeAttribute('Impuesto', '002');
          $xml->writeAttribute('TipoFactor', 'Tasa');
          $xml->writeAttribute('TasaOCuota', number_format($ivaRate, 6, '.', ''));
          $xml->writeAttribute('Importe', $formatSatDecimals($iva));
          $xml->endElement();

          $addTrasladoResumen('002', $ivaRate, $baseIVA, $iva);
        }

        $xml->endElement();
        $xml->endElement();
      endif;

      $xml->endElement();
    endforeach;
  }

  $xml->endElement();
  /*Fin nodo Conceptos */
  if (count($trasladoResumen) > 0) {
    $totalImpuestosTrasladadosCents = 0;
    foreach ($trasladoResumen as $traslado) {
      $totalImpuestosTrasladadosCents += (int) round(((float) $formatMoney2($traslado['importe'])) * 100);
    }

    //$totalImpuestosTrasladados = $ivaTotal;

    /*Inicio nodo Impuestos*/
    $xml->startElementNs('cfdi', 'Impuestos', null);
    $xml->writeAttribute('TotalImpuestosTrasladados', $formatMoney2($totalImpuestosTrasladadosCents / 100));
    $xml->startElementNs('cfdi', 'Traslados', null);

    foreach ($trasladoResumen as $traslado) {
      $xml->startElementNs('cfdi', 'Traslado', null);
      $xml->writeAttribute('Impuesto', $traslado['impuesto']);
      $xml->writeAttribute('TipoFactor', 'Tasa');
      $xml->writeAttribute('TasaOCuota', $traslado['rate']);
      $xml->writeAttribute('Importe', $formatMoney2($traslado['importe']));
      $xml->writeAttribute('Base', $formatMoney2($traslado['base']));
      $xml->endElement();
    }

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

  //file_put_contents($rfc_emisor . '_cfdi.xml', $cfdi);
  //die;
  return $cfdi;
}

function GenerarPDF($comprobante, $numero_certificado, $rfc_emisor, $nombreComprobante)
{
  global $folioInt, $total, $formaPDF, $metodoPDF, $descuentoTotal,  $subtotal, $iepsTotal, $ivaTotal, $idCliente, $tipoFactura, $db, $dataProductos,
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
      for ($i = 0; $i < count($data); $i++) {
        $lines = $this->NbLines($this->widths[$i], $data[$i]);
        // Contar saltos de línea explícitos adicionales
        $explicitNewlines = substr_count($data[$i], "\n");
        $nb = max($nb, $lines + $explicitNewlines);
      }
      $h = 5 * $nb;
      $this->CheckPageBreak($h);
      for ($i = 0; $i < count($data); $i++) {
        $w = $this->widths[$i];
        $a = isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
        $x = $this->GetX();
        $y = $this->GetY();
        $this->Rect($x, $y, $w, $h, $style);
        $this->MultiCell($w, 4, $data[$i], 'LTR', $a, $fill);
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
      global $dataSucursal;

      $this->SetFont('Arial', 'B', 8);
      $this->Cell(20);
      $this->Image(INVOICE_LOGO, 10, 5, INVOICE_LOGO_WIDTH, INVOICE_LOGO_HEIGHT, 'PNG');
      $this->Ln(10);
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
      $this->Ln(2);
      //$this->SetFont('Arial', '', 8);
      $this->Cell(20);
      $this->SetX(10);
      //$this->Cell(0, 0, utf8_decode($dataEmisor['direccion']), 0, 0);
      $pageWidth = $this->GetPageWidth();
      $halfWidth = ($pageWidth / 2) - 20; // Restar el margen izquierdo
      $domicilioFiscal = "DOMICILIO FISCAL: " . utf8_decode($dataEmisor['direccion']);
      $this->MultiCell($halfWidth, 4, $domicilioFiscal, 0, 'L');
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $pageWidth = $this->GetPageWidth();
      $expedidoEn = "EXPEDIDO EN: " . utf8_decode($dataSucursal['direccion']);
      $this->MultiCell($halfWidth, 4, $expedidoEn, 0, 'L');
      $this->SetFont('Arial', '', 8);
    }
    function Footer()
    {
      $this->SetY(-25);
      $pageWidth = $this->GetPageWidth();
      $imageWidth = $pageWidth * 0.5; // 50% del ancho de la página
      $x = ($pageWidth - $imageWidth) / 2;
      //$this->Image(BASE_PATH . "/src/assets/images/pdf-factura-footer.png", $x, null, $imageWidth, 0, 'PNG');
    }
  }


  /*///////////////////////////
        /////// Creación del //////
        ///////     PDF   //////////
        //////////////////////////*/
  // Creación del objeto de la clase heredada
  $xml = simplexml_load_file($comprobante);
  error_log("EL COMPROBANTE ES: " . $comprobante);
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

  //agregando el FOLIO interno
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetXY(140, 4);

  /* $pdf->Cell(60, 5, "NOTA DE CRÉDITO", 1, 0, "L", false);
  $pdf->Ln(5);

  $pdf->Cell(60, 5, "COMPROBANTE FISCAL DIGITAL", 1, 0, "L", false);
  $pdf->Ln(5); */

  /**
   * Los datos de las condicionales tienen un border, quiero quitarlo
   */
  global $folioSerie;
  global $folioInt;

  $pdf->SetFont('Arial', 'B', 12);
  $pdf->SetDrawColor(255, 255, 255);
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);

  $pageWidth  = $pdf->GetPageWidth();
  $boxMargin  = 4;
  $boxWidth   = ($pageWidth / 2);

  $rightBoxX      = $boxWidth + $boxMargin;
  $rightBoxWidth  = $boxWidth - 14;

  $pdf->setX($rightBoxX);

  if ($tipoFactura == "E") :
    $pdf->Cell(60, 5, utf8_decode("NOTA DE CRÉDITO"), 1, 0, "L", false);
    $pdf->Ln(5);
  else :
    $pdf->Cell(60, 5, utf8_decode("FACTURA"), 1, 0, "L", false);
    $pdf->Ln(5);
  endif;

  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(60, 5, "COMPROBANTE FISCAL DIGITAL", 1, 0, "L", false);
  $pdf->Ln(10);

  $pdf->SetFont('Arial', '', 8);

  $pdf->SetDrawColor(224, 224, 224);

  /**
   * START SERIE
   */
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "SERIE", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $folioSerie, 1, 0, "L");
  $pdf->Ln(5);
  /**
   * END SERIE
   */

  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "FOLIO", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $folioInt, 1, 0, "L");
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
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "FOLIO FISCAL", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $UUID, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "NO. DE SERIE DE CERTIFICADO DEL SAT", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $certificadoSAT, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "NO. DE SERIE DE CERTIFICADO DEL CSD", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $dataEmisor['NumCertificado'], 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "FECHA DE CERTIFICACION", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell($rightBoxWidth, 5, $fechaTimbrado, 1, 0, "L");
  $pdf->Ln(5);
  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', '', 8);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell($rightBoxWidth, 5, "FECHA DE ELABORACION", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX($rightBoxX);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(60, 5,utf8_decode($emisor['Municipio']).", ".utf8_decode($emisor['Estado']).", ".date('Y-m-d'),1,0,"L");
  $pdf->Cell($rightBoxWidth, 5, $fecha, 1, 0, "L");
  $pdf->Ln(30);
  $pdf->SetY(55);
  $pdf->SetFont('Arial', '', 10);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(85, 5, "RECEPTOR", 0, 0, "L", true);
  $pdf->Ln();
  $pdf->SetFont('Arial', '', 8);

  /**
   * Obtener los datos del receptor
   */
  global $mysqli;
  global $_POST;

  /* Nombre */
  /* RFC */
  /* Regimen fiscal */
  /* Domicilio fiscal */
  /* Expedido en (Domicilio fiscal también) */
  /* Lugar de expedición (CP) */
  $customerId = $_POST['id_cliente'];

  global $domicilioFiscalReceptor, $regimenFiscalReceptor;

  $query = "SELECT
      C.nombre_completo,
      C.nombre_comercial,
      C.razon_social,
      C.rfc,
      C.id_regimen_fiscal,
      C.domicilio_fiscal,
      R.regimen_fiscal
    FROM
      paal_clientes AS C
    LEFT JOIN
      regimen_fiscal AS R ON (C.id_regimen_fiscal = R.id_regimen_fiscal)
    WHERE
      C.id_cliente = {$customerId}
    LIMIT 1
  ";

  $query_result = mysqli_query($mysqli, $query);
  $data         = mysqli_fetch_assoc($query_result);

  global $db_dti;

  $cfdiQuery = "SELECT
      descripcion
    FROM
      {$db_dti}_uso_cfdi
    WHERE
      id = '{$_POST['id_uso_cfdi']}'
    LIMIT 1
  ";

  $cfdiResult = mysqli_query($mysqli, $cfdiQuery);
  $cfdiData   = mysqli_fetch_assoc($cfdiResult);
  $receptorUsoCFDI_PDF = $cfdiData['descripcion'];

  // Regimen fiscal receptor
  $rfQuery = "SELECT
      concat(id_regimen_fiscal,' - ',regimen_fiscal) AS regimen_fiscal
    FROM
      regimen_fiscal
    WHERE
      id_regimen_fiscal = '{$regimenFiscalReceptor}'
    LIMIT 1
  ";

  $rfResult               = mysqli_query($mysqli, $rfQuery);
  $rfData                 = mysqli_fetch_assoc($rfResult);
  $regimenFiscalReceptor  = $rfData['regimen_fiscal'];

  $receptorInfo = "";
  $receptorInfo .= "Nombre: {$receptorNombre}\n";
  $receptorInfo .= "RFC: {$receptorRFC}\n";
  $receptorInfo .= "Regimen Fiscal: {$regimenFiscalReceptor}\n";
  $receptorInfo .= "Domicilio Fiscal: {$domicilioFiscalReceptor}\n";
  $receptorInfo .= "Uso del CFDI: {$receptorUsoCFDI_PDF}";

  $pdf->MultiCell(110, 5, utf8_decode($receptorInfo), 0, "L");

  //$pdf->MultiCell(110, 5, utf8_decode($receptorNombre) . "\n" . utf8_decode($receptorRFC) . "\n" . utf8_decode('Uso del CFDI: ' . $receptorUsoCFDIPDF), 0, "L");
  //$pdf->SetXY(0, 65);
  $pdf->SetY(85);
  $pdf->Ln(20);

  $tableHeader = [
    ["Cant", 15, "C"],
    ["Unidad", 20, "C"],
    ["Cve. Prod", 25, "C"],
    [utf8_decode("Descripción"), 70, "C"],
    ["Cve. Sat", 15, "C"],
    ["Precio unit.", 25, "R"],
    ["Importe", 20, "R"]
  ];

  $tableWidths = array_map(function ($header) {
    return $header[1];
  }, $tableHeader);

  $tableAligns = array_map(function ($header) {
    return $header[2];
  }, $tableHeader);

  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFillColor(0, 0, 0);

  foreach ($tableHeader as $header) {
    $pdf->Cell($header[1], 4, $header[0], 1, 0, $header[2], true);
  }

  $pdf->SetFont('Arial', '', 6);
  $pdf->Ln(4);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(10,5,"1.0",1,0,"L");

  /** lista las lineas de articulos  **/
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetWidths($tableWidths);
  $pdf->SetAligns($tableAligns);
  $iepsTotalFromPost = $_POST['totalIEPS'] ?? null;
  $usePostedIepsTotal = ($iepsTotalFromPost !== null && $iepsTotalFromPost !== '');
  $iepsTotal = $usePostedIepsTotal
    ? doubleval($iepsTotalFromPost)
    : doubleval($iepsTotal ?? 0);

  if (isset($_POST['uidVenta']) and $_POST['uidVenta'] != '') {

    while ($dataProductos = $db->FetchArray()) {
      if (!$usePostedIepsTotal) {
        $iepsTotal += doubleval($dataProductos['iepsCurrency'] ?? $dataProductos['ieps'] ?? 0);
      }

      $claveProdServ = ($dataProductos['ClaveProdServ'] == '') ? '31231400' : $dataProductos['ClaveUnidad'];
      $claveUnidad = ($dataProductos['ClaveUnidad'] == '') ? 'H87' : $dataProductos['ClaveUnidad'];

      $pdf->Row(array(
        utf8_decode($dataProductos['Cantidad']),
        utf8_decode($claveUnidad),
        utf8_decode($claveProdServ),
        utf8_decode($dataProductos['Producto']),
        utf8_decode($dataProductos['SubTotal']),
        utf8_decode($dataProductos['Total']),
        utf8_decode($dataProductos['Descuento']),
        utf8_decode($dataProductos['ivaCurrency']),
      ));
    }
  } else {
    global $_POST;
    global $facturacionHelpers;

    $productos = $_POST['productos'];
    $productos = json_decode($productos, true);

    foreach ($productos as $producto) :
      $claveProdServ  = $facturacionHelpers->getkeyProductoServicio($producto['keyProductServiceId']);
      $prodServ       = $facturacionHelpers->getNameProductoServicio($producto['keyProductServiceId']);
      $claveUnidad    = $facturacionHelpers->getkeyUnidad($producto['unitMeasurementId']);
      $satUnidad      = $facturacionHelpers->getNameUnidad($producto['unitMeasurementId']);

      $cantidad       = $producto['quantity'];
      $descripcion    = $producto['productName'];
      $valorUnitario  = $producto['priceWithoutIVA'];
      $descuento      = $producto["discount"];
      $importe        = $producto['amountWithoutIVA'];
      $ieps           = $producto['iepsCurrency'] ?? 0;
      $iva            = $producto['ivaCurrency'];
      $serialNumbers  = $producto['serialNumbers'] ?? [];
      $serialNumbersLength = count($serialNumbers);

      if (!$usePostedIepsTotal) {
        $iepsTotal += doubleval($ieps);
      }

      if ($serialNumbersLength > 0) $descripcion .= "\nS/N: " . implode(" ", $serialNumbers);

      $query = "SELECT codigo FROM paal_productos WHERE id_producto = {$producto['productId']}";
      $result = mysqli_query($mysqli, $query);
      $data = mysqli_fetch_assoc($result);
      $productSku = $data['codigo'];

      $tableRow = [
        utf8_decode($cantidad),
        utf8_decode($satUnidad),
        utf8_decode($productSku),
        utf8_decode($descripcion),
        utf8_decode($claveProdServ),
        utf8_decode("$" . number_format($valorUnitario, DECIMALS_CURRENCY)),
        utf8_decode("$" . number_format($importe, DECIMALS_CURRENCY))
      ];

      $pdf->Row($tableRow);
    endforeach;
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

  $rightCellX     = $pdf->GetPageWidth() / 1.355;
  $leftCellWidth  = 25;
  $rightCellWidth = 20;
  $leftInfoWidth  = max(90, $rightCellX - 12);
  $cellHeight     = 4;
  $summaryStartY  = $pdf->GetY();

  $pdf->SetXY(10, $summaryStartY);
  $pdf->Cell($leftInfoWidth, $cellHeight, "Cantidad con letra:  " . numtoletras(truncateDecimals($total)), 0, 0, "L");

  $pdf->SetXY($rightCellX, $summaryStartY);
  $pdf->Cell($leftCellWidth, $cellHeight, "Subtotal:", 1, 0, "R");
  $pdf->Cell($rightCellWidth, $cellHeight, "$" . number_format(truncateDecimals($subtotal), 2, ".", ","), 1, 0, "R");

  $pdf->SetXY(10, $summaryStartY + $cellHeight);
  $pdf->Cell($leftInfoWidth, $cellHeight, "Forma de Pago: " . utf8_decode($formaPDF), 0, 0, "L");

  /* $pdf->SetX(145);
        $pdf->Cell(30,5,utf8_decode("ISH 2.00%"),1,0,"R");
        $pdf->Cell(25,5,number_format($ishTotal,2,".",","),1,0,"R");
        $pdf->Ln(5); */

  $pdf->SetXY($rightCellX, $summaryStartY + $cellHeight);
  $pdf->Cell($leftCellWidth, $cellHeight, "IEPS:", 1, 0, "R");
  $pdf->Cell($rightCellWidth, $cellHeight, "$" . number_format(truncateDecimals($iepsTotal), 2, ".", ","), 1, 0, "R");

  $pdf->SetXY($rightCellX, $summaryStartY + ($cellHeight * 2));
  $pdf->Cell($leftCellWidth, $cellHeight, "IVA 16.00%", 1, 0, "R");
  $pdf->Cell($rightCellWidth, $cellHeight, "$" . number_format(truncateDecimals($ivaTotal), 2, ".", ","), 1, 0, "R");

  $pdf->SetXY($rightCellX, $summaryStartY + ($cellHeight * 3));
  $pdf->Cell($leftCellWidth, $cellHeight, "TOTAL:", 1, 0, "R");
  $pdf->Cell($rightCellWidth, $cellHeight, "$" . number_format(truncateDecimals($total), 2, ".", ","), 1, 0, "R");

  $pdf->SetXY(10, $summaryStartY + ($cellHeight * 2));
  $pdf->Cell($leftInfoWidth, $cellHeight, utf8_decode("Método de Pago: " . $metodoPDF), 0, 0, "L");

  $pdf->SetY($summaryStartY + ($cellHeight * 4));
  // $pdf->setX($rightCellX);
  // $pdf->Cell($leftCellWidth, $cellHeight, "Descuento:", 1, 0, "R");
  // $pdf->Cell($rightCellWidth, $cellHeight, "$" . number_format(truncateDecimals($descuentoTotal), 2, ".", ","), 1, 0, "R");
  // $pdf->Ln($cellHeight);

  // Agregar los CFDIs relacionados aquí
  global $arrayRelationTypes;

  $cfdi_relacionado = $_POST['cfdi_relacionado'] ?? [];
  $tipo_relacion = $_POST['tipo_relacion'] ?? '';

  if ($tipo_relacion != '' && count($cfdi_relacionado) > 0) {
    $pdf->Ln(0);
    $tipo_relacion_text = $arrayRelationTypes[$tipo_relacion] ?? 'Tipo de relación desconocido';
    $pdf->MultiCell(0, $cellHeight, utf8_decode("Tipo de Relación: " . $tipo_relacion_text), 0, "L");
    $cfdi_relacionado_text = implode(", ", $cfdi_relacionado);
    $pdf->MultiCell(0, $cellHeight, utf8_decode("CFDIs Relacionados: " . $cfdi_relacionado_text), 0, "L");
    $pdf->Ln($cellHeight);
  }

  $comentarios = $_POST['comentarios'] ?? '';
  if (trim($comentarios) != '') {
    $pdf->Ln(0);
    $pdf->MultiCell(0, $cellHeight, utf8_decode("Comentarios: " . $comentarios), 0, "L");
  }


  //$pdf->Cell(140, 5, utf8_decode("Objeto de impuesto: " . $objetoImpuestoPDF), 0, 0, "L");


  $pageWidthMinusMargins = $pdf->GetPageWidth() - 53;

  $pdf->Ln(5);
  $pdf->Cell(0, 0, "Este documento es una representacion impresa de un CFDI", 0, 0, "L");
  $pdf->Ln(5);

  $pdf->Image(FCS_CARPETA_PATH . $UUID . '.png', 10, null, 30, 30, 'PNG');
  $pdf->SetFont('Arial', '', 5);

  $pdf->SetY($pdf->GetY() - 30);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 3, utf8_decode("Sello Timbre") . "\n||" . $version . "|" . $UUID . "|" . $fechaTimbrado . "|" . $CFD . "|" . $certificadoSAT, 0, 'L');

  //$pdf->Ln(3);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 3, "Sello del SAT\n" . $SAT, 0, "L");

  //$pdf->Ln(3);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 3, "Sello Digital\n" . $CFD, 0, "L");

  $archivo_PDF = FCS_CARPETA_PATH . $UUID . ".pdf";
  $pdf->Output($archivo_PDF, 'F');

  $nombreArchivoPDF = $UUID . ".pdf";

  /**
   * Guardar la factura en la DB
   */
  global $mysqli;

  $id_emisor      = $dataEmisor["id_emisor"];
  $id_cliente     = $idCliente;
  $id_uso_cfdi    = $_POST["id_uso_cfdi"];
  $id_forma_pago  = $_POST["id_forma_pago"];
  $id_venta       = $_POST['idVenta'] ? cleanStr($_POST['idVenta']) : "NULL";
  $serie          = $folioSerie;
  $folio          = $folioInt;
  $uuid           = $UUID;
  $metodo_pago    = ($_POST['metodo_pago'] == '') ? 'PUE' : $_POST['metodo_pago'];
  $moneda         = "MXN";
  $totalf         = number_format((float) $total, 2, '.', '');
  $comentarios    = $_POST['comentarios'] ?? 'Sin comentarios';
  $fecha          = date('Y-m-d', strtotime($_POST['fecha_emision']));
  $tipoFactura    = $_POST['tipo_factura'];
  $tipoRelacion  = $_POST['tipo_relacion'];
  $cfdi_relacionado = $_POST['cfdi_relacionado'] ?? [];
  $id_sucursal    = $_POST['id_sucursal'];

  $cfdi_relacionado = implode(',', $cfdi_relacionado);

  /**
   * Insertar la factura
   */
  $invoiceModel = new FacturasNotasCreditoModel();
  $invoiceModel->setIssuerId($id_emisor);
  $invoiceModel->setCustomerId($id_cliente);
  $invoiceModel->setUseCfdiId($id_uso_cfdi);
  $invoiceModel->setPaymentFormId($id_forma_pago);
  $invoiceModel->setSaleId($id_venta);
  $invoiceModel->setSerie($serie);
  $invoiceModel->setFolio($folio);
  $invoiceModel->setUuid($uuid);
  $invoiceModel->setPaymentMethod($metodo_pago);
  $invoiceModel->setCurrency($moneda);
  $invoiceModel->setTotal($totalf);
  $invoiceModel->setComments($comentarios);
  $invoiceModel->setDate($fecha);
  $invoiceModel->setRelationType($tipoRelacion);
  $invoiceModel->setCfdiRelated($cfdi_relacionado);
  $invoiceModel->setBranchId($id_sucursal);

  $result = $invoiceModel->create();

  $saleUids       = $_POST['saleUids'] ?? null;

  // Relacionar la factura con las ventas correspondientes
  if ($id_venta && $id_venta != 0 && $id_venta != "NULL") $saleUids = $id_venta;

  if ($result->status == "success" && $saleUids) {
    require_once __DIR__ . "/../lib/models/venta-facturas.model.php";

    $saleInvoicesModel  = new SaleInvoicesModel();
    $saleUids           = $saleUids ? explode(",", $saleUids) : [];
    $invoiceId          = $invoiceModel->getId();

    foreach ($saleUids as $saleUid) {
      if (!$saleUid || $saleUid == 0 || $saleUid === "NULL" || $saleUids == null) continue;

      error_log("Relacionando factura {$invoiceId} con la venta {$saleUid}");

      $saleInvoicesModel->setSaleId($saleUid);
      $saleInvoicesModel->setInvoiceId($invoiceId);
      $saleInvoicesModel->setType("nota_credito");
      $saleInvoicesModel->create();
    }
  }

  if (isset($_POST['enviar_al_correo']) and  $_POST['enviar_al_correo'] == 'si' and  trim($_POST['correo']) != '') :
    $sender       = new stdClass();
    $sender->name = $dataEmisor["Nombre"];

    $customer = new stdClass();
    $customer->rfc    = $_POST['cliente_rfc'];
    $customer->name   = $_POST['razon_social'];
    $customer->emails = [
      [
        "name"  => $_POST['razon_social'],
        "email" => $_POST["correo"]
      ]
    ];

    $xml = $comprobante;
    $pdf = $archivo_PDF;

    if ($customer->emails) :
      try {
        $mail = new PHPMailer();

        $sendResponse = sendInvoice(
          $mail,
          $sender,
          $customer,
          $xml,
          $pdf
        );

        error_log(json_encode($sendResponse));

        if ($sendResponse["status"] == "success") :
          $invoiceModel->setSent(1);
          $invoiceModel->update();
        endif;
      } catch (Exception $e) {
        error_log("Envío de facura error: {$e->getMessage()}");
      }
    endif;
  endif;

  //insertamos el nuevo folio consumido
  //$db->Exec("INSERT INTO facturacion_folios(Folio,Fecha,idUserCaptura) VALUES ('".$folioInt."',NOW(),'".$_SESSION["Med_idUsuario"]."')");


  return $archivo_PDF;
}

TimbrarFactura();
