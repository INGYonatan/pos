<?php
require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Prueba de timbrado con la conexion a Facturacion Moderna
 * @return void
 */

const FCS_CARPETA_URL   = CARPETA_FACTURAS_PAGO_URL;
const FCS_CARPETA_PATH  = CARPETA_FACTURAS_PAGO_PATH;

$result     = mysqli_query($mysqli, "SELECT MAX(folio) as Num FROM paal_facturas_p WHERE serie = '{$folioSerie}'");
$dataFlolio = mysqli_fetch_assoc($result);
$folioInt   = $dataFlolio['Num'] + 1;
$invoiceFolio = $folioInt;
//$paymentFolio = createPaymentInvoiceFolio();

$result       = mysqli_query($mysqli, "SELECT MAX(folio) as Num FROM paal_facturas_p_pagos WHERE serie = '{$folioSerie}'");
$dataFlolio   = mysqli_fetch_assoc($result);
$paymentFolio = $dataFlolio['Num'] + 1;

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

  $file = BASE_PATH . '/src/assets/facturacion/utilerias/xslt40/cadenaoriginal_4_0.xslt';

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
  $fecha_actual = substr(date('c'), 0, 19);
  global $receptorNombre, $receptorRFC, $receptorUsoCFDI, $metodoPago, $formaPago, $subtotal, $descuentoTotal, $ivaTotal, $ishTotal, $total, $folioInt, $dataProductos, $db;

  global $dataEmisor;
  global $domicilioFiscalReceptor;
  global $regimenFiscalReceptor;
  global $dataSucursal;

  global $folioSerie;


  $lugarExpedicion = $dataSucursal["cp"];

  $xml = new XMLWriter();
  $xml->openMemory();
  $xml->startDocument('1.0', 'utf-8');
  $xml->setIndent(true);
  /**Inicio nodo Comprobantes*/
  $xml->startElementNs('cfdi', 'Comprobante', null);
  $xml->writeAttributeNS('xmlns', 'cfdi', null, 'http://www.sat.gob.mx/cfd/4');
  $xml->writeAttributeNS('xmlns', 'xs', null, 'http://www.w3.org/2001/XMLSchema');
  $xml->writeAttributeNS('xmlns', 'xsi', null, 'http://www.w3.org/2001/XMLSchema-instance');
  $xml->writeAttributeNS('xmlns', 'pago20', null, 'http://www.sat.gob.mx/Pagos20');
  $xml->writeAttributeNS('xsi', 'schemaLocation', null, 'http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd');

  /*Atributos nodo Comprobantes*/
  $xml->writeAttribute('Version', '4.0');
  $xml->writeAttribute('Serie', $folioSerie);
  $xml->writeAttribute('Folio', '$folioInt');/*Revisar*/
  $xml->writeAttribute('Fecha', $fecha_actual);

  $xml->writeAttribute('Moneda', 'XXX');
  $xml->writeAttribute('SubTotal', '0');/*Revisar*/
  $xml->writeAttribute('Total', '0');/*Revisar*/
  $xml->writeAttribute('TipoDeComprobante', 'P'); //en la versión anterior era la palabra completa "Ingreso"     
  $xml->writeAttribute('LugarExpedicion', $lugarExpedicion);

  /*Inicio nodo Emisor*/
  $xml->startElementNs('cfdi', 'Emisor', null);
  $xml->writeAttribute('Rfc', $dataEmisor["RFC"]); //$rfcE
  $xml->writeAttribute('Nombre', $dataEmisor["Nombre"]); //$emisor['Nombre']
  $xml->writeAttribute('RegimenFiscal',  $dataEmisor['RegimenFiscalClave']); //$emisor['RegimenFiscal']
  $xml->endElement();
  /*Fin nodo Emisor*/

  /*Inicio nodo Receptor*/
  $xml->startElementNs('cfdi', 'Receptor', null);
  $xml->writeAttribute('Rfc', $receptorRFC); // $cliente['RFC']
  $xml->writeAttribute('Nombre', $receptorNombre); //$cliente['Cliente']
  $xml->writeAttribute('UsoCFDI', $receptorUsoCFDI);/*GASTOS EN GENERAL*/
  $xml->endElement();
  /*Fin nodo receptor*/


  /*Inicio nodo Conceptos */
  $xml->startElementNs('cfdi', 'Conceptos', null);
  $xml->startElementNs('cfdi', 'Concepto', null);
  $xml->writeAttribute('Cantidad', '1');
  $xml->writeAttribute('ClaveProdServ', '84111506');
  $xml->writeAttribute('ClaveUnidad', 'ACT');
  $xml->writeAttribute('Descripcion', 'Pago');/*Revisar*/
  $xml->writeAttribute('ValorUnitario', '0');/*Revisar*/
  $xml->writeAttribute('Importe', '0');/*Revisar*/
  $xml->writeAttribute('ObjetoImp', '01');/*Revisar*/
  $xml->endElement();
  $xml->endElement();
  /*Fin nodo Conceptos */


  $xml->startElementNs('cfdi', 'Complemento', null);
  $xml->startElementNs('pago20', 'Pagos', null);
  $xml->writeAttribute('Version', '2.0');
  $xml->startElementNs('pago20', 'Pago', null);
  $xml->writeAttribute('FechaPago', date('Y-m-d', strtotime($_POST['fecha_pago'])) . "T12:00:00");
  $xml->writeAttribute('FormaDePagoP', $formaPago);
  $xml->writeAttribute('MonedaP', 'MXN');
  $xml->writeAttribute('Monto', str_replace(',', '', number_format($_POST['monto'], DECIMALS_CURRENCY)));
  $xml->startElementNs('pago20', 'DoctoRelacionado', null);
  $xml->writeAttribute('IdDocumento', $_POST['cfdi_relacionado']);
  // $xml->writeAttribute('Folio', $_POST['Folio']); // Este nodo es opcional
  $xml->writeAttribute('MonedaDR', 'MXN');
  $xml->writeAttribute('MetodoDePagoDR', $_POST['metodo_pago']);
  $xml->writeAttribute('NumParcialidad', $_POST['num_parcialidad']);
  $xml->writeAttribute('ImpSaldoAnt', str_replace(',', '', number_format($_POST['importe_saldo_anterior'], DECIMALS_CURRENCY)));
  $xml->writeAttribute('ImpPagado', str_replace(',', '', number_format($_POST['importe_pagado'], DECIMALS_CURRENCY)));
  $xml->writeAttribute('ImpSaldoInsoluto', str_replace(',', '', number_format($_POST['importe_saldo_insoluto'], DECIMALS_CURRENCY)));
  $xml->endElement();
  $xml->endElement();
  $xml->endElement();
  $xml->endElement();

  $xml->endElement();
  /*Fin nodo comprobante*/
  $prexML = $xml->outputMemory(true);
  $cfdi = $prexML;

  $fechaPago = date('Y-m-d', strtotime($_POST['fecha_pago'])) . "T12:00:00";
  $tipoCambio = 1;
  $monto = str_replace(',', '', number_format($_POST['monto'], 2));
  $idDocumento = $_POST['cfdi_relacionado'];
  //$folio = $_POST['Folio'];

  $totalTrasladosBaseIVA16      = number_format(($_POST["monto"] / 1.16), 2, '.', "");
  error_log("totalTrasladosBaseIVA16: " . $totalTrasladosBaseIVA16);
  $totalTrastradosImpuestoIVA16 = $_POST["monto"] - $totalTrasladosBaseIVA16;
  $montoTotalPagos              = $_POST["monto"];

  $objetoImpuestoDR = $_POST['objeto_impuesto_dr'];
  $numParcialidad   = $_POST['num_parcialidad'];
  $impSaldoAnt      = round($_POST['importe_saldo_anterior'], 2);
  $impPagado        = $_POST['importe_pagado'];
  $impSaldoInsoluto = round($_POST['importe_saldo_insoluto'], 2);

  $xmlImpuestosDR   = "";
  $xmlImpuestosP    = "";

  if ($objetoImpuestoDR == "02") :
    $baseDR       = number_format(($_POST["monto"] / 1.16), 2, '.', "");
    $impuestoDR   = $_POST["impuesto_dr"];
    $tipoFactorDR = $_POST["tipo_factor_dr"];
    $tasaOCuotaDR = "0.160000";
    $importeDR    = $_POST["monto"] - $baseDR;

    $xmlImpuestosDR = <<<XML
      <pago20:ImpuestosDR>
        <pago20:TrasladosDR>
          <pago20:TrasladoDR BaseDR="{$baseDR}" ImpuestoDR="{$impuestoDR}" TipoFactorDR="{$tipoFactorDR}" TasaOCuotaDR="{$tasaOCuotaDR}" ImporteDR="{$importeDR}"/>
        </pago20:TrasladosDR>
      </pago20:ImpuestosDR>
    XML;
  endif;

  $baseP        = number_format(($_POST["monto"] / 1.16), 2, '.', "");
  $impuestoP    = $_POST["impuesto_dr"];
  $tipoFactorP  = $_POST["tipo_factor_dr"];
  $tasaOCuotaP  = "0.160000";
  $importeP     = $_POST["monto"] - $baseP;

  global $invoiceFolio;
  global $paymentFolio;
  global $folioSerie;

  $xml = <<<XML
    <cfdi:Comprobante
      xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
      xmlns:xs="http://www.w3.org/2001/XMLSchema"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xmlns:pago20="http://www.sat.gob.mx/Pagos20"
      xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd http://www.sat.gob.mx/Pagos20 http://www.sat.gob.mx/sitio_internet/cfd/Pagos/Pagos20.xsd"
      Version="4.0"
      Serie="{$folioSerie}"
      Folio="{$invoiceFolio}"
      Fecha="{$fecha_actual}"
      SubTotal="0"
      Moneda="XXX"
      Total="0"
      Exportacion="01"
      TipoDeComprobante="P"
      LugarExpedicion="{$lugarExpedicion}"
    >
      <cfdi:Emisor
        Rfc="{$dataEmisor['RFC']}"
        Nombre="{$dataEmisor['Nombre']}"
        RegimenFiscal="{$dataEmisor['RegimenFiscalClave']}"
      />

      <cfdi:Receptor
        Rfc="{$receptorRFC}"
        Nombre="{$receptorNombre}"
        DomicilioFiscalReceptor="{$domicilioFiscalReceptor}"
        RegimenFiscalReceptor="{$regimenFiscalReceptor}"
        UsoCFDI="{$receptorUsoCFDI}"
      />

      <cfdi:Conceptos>
        <cfdi:Concepto ClaveProdServ="84111506" Cantidad="1" ClaveUnidad="ACT" Descripcion="Pago" ValorUnitario="0" Importe="0" ObjetoImp="01"/>
      </cfdi:Conceptos>

      <cfdi:Complemento>
        <pago20:Pagos Version="2.0">
          <pago20:Totales TotalTrasladosBaseIVA16="{$totalTrasladosBaseIVA16}" TotalTrasladosImpuestoIVA16="{$totalTrastradosImpuestoIVA16}" MontoTotalPagos="{$montoTotalPagos}"/>

          <pago20:Pago
            FechaPago="{$fechaPago}"
            FormaDePagoP="{$formaPago}"
            MonedaP="MXN"
            TipoCambioP="1"
            Monto="{$monto}"
          >
            <pago20:DoctoRelacionado
              IdDocumento="{$idDocumento}"
              Serie="{$folioSerie}"
              Folio="{$paymentFolio}"
              MonedaDR="MXN"
              EquivalenciaDR="1"
              ObjetoImpDR="{$objetoImpuestoDR}"
              NumParcialidad="{$numParcialidad}"
              ImpSaldoAnt="{$impSaldoAnt}"
              ImpPagado="{$impPagado}"
              ImpSaldoInsoluto="{$impSaldoInsoluto}"
            >
              {$xmlImpuestosDR}
            </pago20:DoctoRelacionado>

            <pago20:ImpuestosP>
              <pago20:TrasladosP>
                <pago20:TrasladoP BaseP="{$baseP}" ImpuestoP="{$impuestoP}" TipoFactorP="{$tipoFactorP}" TasaOCuotaP="{$tasaOCuotaP}" ImporteP="{$importeP}"/>
              </pago20:TrasladosP>
            </pago20:ImpuestosP>
          </pago20:Pago>
        </pago20:Pagos>
      </cfdi:Complemento>
    </cfdi:Comprobante>
  XML;

  $cfdi = $xml;

  //file_put_contents($rfc_emisor . '_cfdi.xml', $cfdi);
  //die;
  return $cfdi;
}

function GenerarPDF($comprobante, $numero_certificado, $rfc_emisor, $nombreComprobante)
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

  $pdf->SetDrawColor(255, 255, 255);
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);

  $pdf->SetFont('Arial', 'B', 12);

  $pageWidth  = $pdf->GetPageWidth();
  $boxMargin  = 4;
  $boxWidth   = ($pageWidth / 2);

  $rightBoxX      = $boxWidth + $boxMargin;
  $rightBoxWidth  = $boxWidth - 14;

  $pdf->setX($rightBoxX);

  $pdf->Cell(60, 5, utf8_decode("COMPLEMENTO DE PAGO"), 1, 0, "L", false);
  $pdf->Ln(5);

  $pdf->SetX($rightBoxX);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(60, 5, "COMPROBANTE FISCAL DIGITAL", 1, 0, "L", false);
  $pdf->Ln(10);

  $pdf->SetFont('Arial', '', 8);

  $pdf->SetDrawColor(224, 224, 224);

  /**
   * START SERIE
   */
  global $folioSerie;

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
        $pdf->SetX($rightBoxX);
        $pdf->SetTextColor(0,0,0);
        $pdf->Cell($rightBoxWidth, 5,'seriado',1,0,"L");
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

  // Uso de CFDI
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
    [utf8_decode("Descripción"), 95, "C"],
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

  $pdf->SetFont('Arial', '', 6);
  $pdf->SetTextColor(255, 255, 255);
  $pdf->SetFillColor(0, 0, 0);

  foreach ($tableHeader as $header) {
    $pdf->Cell($header[1], 4, $header[0], 1, 0, $header[2], true);
  }

  $pdf->Ln(4);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(10,5,"1.0",1,0,"L");

  /** lista las lineas de articulos  **/
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetWidths($tableWidths);
  $pdf->SetAligns($tableAligns);

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
    global $_POST;
    global $facturacionHelpers;

    $productos = $_POST['productos'];
    $productos = json_decode($productos, true);

    foreach ($productos as $producto) :
      $claveProdServ  = $producto['keyProductServiceId'];
      $claveUnidad    = $producto['unitMeasurementId'];

      $cantidad       = $producto['quantity'];
      $descripcion    = $producto['productName'];
      $valorUnitario  = $producto['priceWithoutIVA'];
      $descuento      = $producto["discount"];
      $importe        = $producto['amountWithoutIVA'];
      $iva            = $producto['ivaCurrency'];
      $serialNumbers  = $producto['serialNumbers'] ?? [];
      $serialNumbersLength = count($serialNumbers);

      if ($serialNumbersLength > 0) $descripcion .= "\nS/N: " . implode(" ", $serialNumbers);

      $query = "SELECT codigo FROM paal_productos WHERE id_producto = {$producto['productId']}";
      $result = mysqli_query($mysqli, $query);
      $data = mysqli_fetch_assoc($result);
      $productSku = $data['codigo'];

      $tableRow = [
        utf8_decode($cantidad),
        utf8_decode($claveUnidad),
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

  /**
   * START TABLE CFDI RELACIONADOS
   * El width de la hoja es 190
   */
  $pdf->SetXY(10, $pdf->GetY() + 10);

  $tableUUID    = $UUID;
  $tableFolio   = "{$folioSerie}p-{$folioInt}";
  $tableMetodo  = $metodoPDF;
  $tableSAnt    = number_format($_POST["importe_saldo_anterior"], DECIMALS_CURRENCY);
  $tableSPagado = number_format($_POST["monto"], DECIMALS_CURRENCY);
  $tableSPend   = number_format($_POST["importe_saldo_insoluto"], DECIMALS_CURRENCY);

  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(255, 168, 0);
  $pdf->Cell(60, 5, "UUID", 1, 0, "L", true);
  $pdf->Cell(20, 5, "FOLIO", 1, 0, "C", true);
  $pdf->Cell(35, 5, utf8_decode("MÉTODO"), 1, 0, "L", true);
  $pdf->Cell(25, 5, utf8_decode("S. ANTERIOR"), 1, 0, "R", true);
  $pdf->Cell(25, 5, "S. PAGADO", 1, 0, "R", true);
  $pdf->Cell(25, 5, "S. PENDIENTE", 1, 0, "R", true);
  $pdf->Ln(5);
  $pdf->SetTextColor(0, 0, 0);

  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetWidths(array(60, 20, 35, 25, 25, 25));
  $pdf->SetAligns(array("L", "C", "L", "R", "R", "R"));

  $pdf->Row(array(
    utf8_decode($tableUUID),
    utf8_decode($tableFolio),
    utf8_decode($tableMetodo),
    utf8_decode("$" . $tableSAnt),
    utf8_decode("$" . $tableSPagado),
    utf8_decode("$" . $tableSPend)
  ));

  /**
   * END TABLE CFDI RELACIONADOS
   */

  /**
   * START parte final de la tabla
   */
  $pdf->SetXY(10, $pdf->GetY() + 4);
  $pdf->Cell(140, 4, "Cantidad con letra:  " . numtoletras($tableSPagado), 0, 0, "L");
  $pdf->SetXY(10, $pdf->GetY() + 4);
  $pdf->Cell(140, 4, "Forma de Pago: " . utf8_decode($formaPDF), 0, 0, "L");
  $pdf->SetXY(10, $pdf->GetY() + 4);
  $pdf->Cell(140, 4, utf8_decode("Método de Pago: " . $metodoPDF), 0, 0, "L");
  // Agregar los CFDIs relacionados aquí
  $cfdi_relacionado = $_POST['cfdi_relacionado'] ?? "";

  if ($cfdi_relacionado) {
    $pdf->Ln(4);
    $pdf->MultiCell(0, 4, utf8_decode("CFDI Relacionado: " . $cfdi_relacionado), 0, "L");
  }

  /**
   * END parte final de la tabla
   */

  $comentarios = $_POST['comentarios'] ?? '';
  if (trim($comentarios) != '') {
    $pdf->Ln(0);
    $pdf->MultiCell(0, 5, utf8_decode("Comentarios: " . $comentarios), 0, "L");
  }
  /**
   * END parte final de la tabla
   */


  //$pdf->Cell(140, 5, utf8_decode("Objeto de impuesto: " . $objetoImpuestoPDF), 0, 0, "L");

  $pageWidthMinusMargins = $pdf->GetPageWidth() - 53;

  $pdf->Ln(4);
  $pdf->Cell(0, 0, "Este documento es una representacion impresa de un CFDI", 0, 0, "L");
  $pdf->Ln(4);

  $pdf->Image(FCS_CARPETA_PATH . $UUID . '.png', 10, null, 30, 30, 'PNG');
  $pdf->SetFont('Arial', '', 5);

  $pdf->SetY($pdf->GetY() - 30);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 4, utf8_decode("Sello Timbre") . "\n||" . $version . "|" . $UUID . "|" . $fechaTimbrado . "|" . $CFD . "|" . $certificadoSAT, 0, 'L');

  //$pdf->Ln(3);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 4, "Sello del SAT\n" . $SAT, 0, "L");

  //$pdf->Ln(3);
  $pdf->SetX(43);
  $pdf->MultiCell($pageWidthMinusMargins, 4, "Sello Digital\n" . $CFD, 0, "L");

  $archivo_PDF = FCS_CARPETA_PATH . $UUID . ".pdf";
  $pdf->Output($archivo_PDF, 'F');

  $nombreArchivoPDF = $UUID . ".pdf";

  global $mysqli;
  global $invoiceFolio;
  global $paymentFolio;
  global $_POST;
  global $folioSerie;

  $cfdi_relacionado = $_POST['cfdi_relacionado'];

  $query = "SELECT id_factura FROM paal_facturas WHERE uuid = '{$cfdi_relacionado}'";
  $result = mysqli_query($mysqli, $query);
  $row = mysqli_fetch_assoc($result);
  $idFacturaRelacionada = $row['id_factura'];
  $fechaPago = date('Y-m-d', strtotime($_POST['fecha_pago'])) . "T" . date('H:i:s');
  $id_sucursal    = $_POST['id_sucursal'];
  $comentarios    = mysqli_real_escape_string($mysqli, $_POST['comentarios'] ?? '');

  $invoiceResult = mysqli_query($mysqli, "INSERT INTO paal_facturas_p (
      id_factura_ingreso,
      id_emisor,
      id_cliente,
      folio,
      uuid,
      fecha,
      id_sucursal,
      serie,
      comentarios
    ) VALUES (
      '{$idFacturaRelacionada}',
      '{$dataEmisor['id_emisor']}',
      '{$idCliente}',
      '{$invoiceFolio}',
      '{$UUID}',
      '{$fechaPago}',
      '{$id_sucursal}',
      '{$folioSerie}',
      '{$comentarios}'
    )
  ");

  $invoiceId = mysqli_insert_id($mysqli);

  $folioPagoSerie = "{$folioSerie}p";

  $invoiceResult = mysqli_query($mysqli, "INSERT INTO paal_facturas_p_pagos (
      id_factura,
      id_forma_pago,
      folio,
      metodo_pago,
      monto,
      num_parcialidad,
      importe_saldo_anterior,
      importe_pagado,
      importe_saldo_insoluto,
      objeto_impuesto_dr,
      impuesto_dr,
      tipo_factor_dr,
      fecha,
      serie,
      comentarios
    ) VALUES (
      '{$invoiceId}',
      '{$_POST['id_forma_pago']}',
      '{$paymentFolio}',
      '{$_POST['metodo_pago']}',
      '{$_POST['monto']}',
      '{$_POST['num_parcialidad']}',
      '{$_POST['importe_saldo_anterior']}',
      '{$_POST['importe_pagado']}',
      '{$_POST['importe_saldo_insoluto']}',
      '{$_POST['objeto_impuesto_dr']}',
      '{$_POST['impuesto_dr']}',
      '{$_POST['tipo_factor_dr']}',
      '{$fechaPago}',
      '{$folioPagoSerie}',
      '{$comentarios}'
    )
  ");

  if ($_POST["idVentaPago"]) mysqli_query($mysqli, "INSERT INTO paal_venta_pago_facturas (id_venta_pago, id_factura) VALUES ({$_POST["idVentaPago"]}, {$invoiceId})");

  /* $result = mysqli_query($mysqli, "INSERT INTO paal_facturas (Fecha,Folio,UUID,PDF,XML,idCliente,Cliente,Emisor,idEmisor,EmisorRFC,Importe,TipoFactura)
                                VALUES (NOW(),'" . $folioInt . "','" . $UUID . "','" . $nombreArchivoPDF . "','" . $nombreComprobante . "','" . $idCliente . "','" . $receptorNombre . "','" . $dataEmisor['Nombre'] . "','" . $dataEmisor['id_emisor'] . "','" . $dataEmisor['RFC'] . "','" . $total . "','I')"); */

  $id_factura   = $invoiceId;

  if (isset($_POST['enviar_al_correo']) and  $_POST['enviar_al_correo'] == 'si' and  trim($_POST['correo']) != '') {
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

        if ($sendResponse["status"] == "success") mysqli_query($mysqli, "UPDATE paal_facturas_p SET enviado = 1 WHERE id_factura = {$id_factura}");
      } catch (Exception $e) {
        error_log("Envío de facura error: {$e->getMessage()}");
      }
    endif;
  }

  //insertamos el nuevo folio consumido
  //$db->Exec("INSERT INTO facturacion_folios(Folio,Fecha,idUserCaptura) VALUES ('".$folioInt."',NOW(),'".$_SESSION["Med_idUsuario"]."')");


  return $archivo_PDF;
}

TimbrarFactura();
