<?php
const FCS_CARPETA_URL   = CARPETA_FACTURAS_TRASLADO_URL;
const FCS_CARPETA_PATH  = CARPETA_FACTURAS_TRASLADO_PATH;

/**
 * Prueba de timbrado con la conexion a Facturacion Moderna
 * @return void
 */
function TimbrarFactura()
{
  /**
   * @var EmisoresHelper $emisor
   */
  global $emisor;
  $debug = 1;

  $rfc_emisor         = $emisor->getRfc();
  $numero_certificado = $emisor->getCertificateNumber();

  if (!$numero_certificado) :
    echo json_encode([]);
    die;
  endif;

  $archivo_cer        = FACTURAS_CERTIFICADO_PATH . $emisor->getCerFile();
  $archivo_pem        = FACTURAS_CERTIFICADO_PATH . $emisor->getKeyFile();

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
  global $folio, $fechaXML, $subtotal, $moneda, $total, $tipoComprobante, $lugarExpedicion;

  global $emisorRFC, $emisorNombre, $emisorRegimenFiscal;

  global $receptorRFC, $receptorNombre, $domicilioFiscalReceptor, $regimenFiscalReceptor, $usoCFDI;

  global $conceptos;

  # Agregar conceptos
  # Apertura
  $conceptosxml = <<<XML
    <cfdi:Conceptos>
  XML;

  // conceptos @pending
  foreach ($conceptos as $concepto) :
    $objetoImpuesto   = "01";
    $claveProdServ    = $concepto['clave_producto_servicio'];
    $noIdentificacion = $concepto['codigo'];
    $cantidad         = $concepto['cantidad'];
    $claveUnidad      = $concepto['clave_unidad'];
    $unidad           = /* $concepto['unidad'] */ "PZ";
    $descripcion      = $concepto['nombre_producto'];
    $valorUnitario    = "0";
    $importe          = "0";
    $tipoConcepto     = $concepto['tipo'];


    // Solo los productos tipo "equipo" manejan números de serie
    if ($tipoConcepto != "equipo") $conceptosxml .= <<<XML
      <cfdi:Concepto
        ObjetoImp="{$objetoImpuesto}"
        ClaveProdServ="{$claveProdServ}"
        NoIdentificacion="{$noIdentificacion}"
        Cantidad="{$cantidad}"
        ClaveUnidad="{$claveUnidad}"
        Unidad="{$unidad}"
        Descripcion="{$descripcion}"
        ValorUnitario="{$valorUnitario}"
        Importe="{$importe}"
      />
    XML;

    if ($tipoConcepto == "equipo") :
      $conceptosxml .= <<<XML
        <cfdi:Concepto
          ObjetoImp="{$objetoImpuesto}"
          ClaveProdServ="{$claveProdServ}"
          NoIdentificacion="{$noIdentificacion}"
          Cantidad="{$cantidad}"
          ClaveUnidad="{$claveUnidad}"
          Unidad="{$unidad}"
          Descripcion="{$descripcion}"
          ValorUnitario="{$valorUnitario}"
          Importe="{$importe}"
        >
      XML;

      foreach ($concepto['serial_numbers'] as $serial_number) :
        $conceptosxml .= <<<XML
          <cfdi:Parte
            ClaveProdServ="{$claveProdServ}"
            NoIdentificacion="{$serial_number}"
            Cantidad="1"
            Unidad="{$unidad}"
            Descripcion="{$descripcion}"
            ValorUnitario="1"
            Importe="1"
          />
        XML;
      endforeach;

      $conceptosxml .= <<<XML
        </cfdi:Concepto>
      XML;
    endif;
  endforeach;

  # Cierre
  $conceptosxml .= <<<XML
    </cfdi:Conceptos>
  XML;

  $xml = <<<XML
    <cfdi:Comprobante
      xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
      xmlns:xs="http://www.w3.org/2001/XMLSchema"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd"
      Version="4.0"
      Folio="{$folio}"
      Fecha="{$fechaXML}"
      SubTotal="{$subtotal}"
      Moneda="{$moneda}"
      Total="{$total}"
      Exportacion="01"
      TipoDeComprobante="{$tipoComprobante}"
      LugarExpedicion="{$lugarExpedicion}"
    >
      <cfdi:Emisor
        Rfc="{$emisorRFC}"
        Nombre="{$emisorNombre}"
        RegimenFiscal="{$emisorRegimenFiscal}"
      />

      <cfdi:Receptor
        Rfc="{$receptorRFC}"
        Nombre="{$receptorNombre}"
        DomicilioFiscalReceptor="{$domicilioFiscalReceptor}"
        RegimenFiscalReceptor="{$regimenFiscalReceptor}"
        UsoCFDI="{$usoCFDI}"
      />

      {$conceptosxml}
    </cfdi:Comprobante>
  XML;


  $cfdi = $xml;

  //file_put_contents($emisorRFC . '_cfdi.xml', $cfdi);
  //die;
  return $cfdi;
}

function GenerarPDF($comprobante, $numero_certificado, $rfc_emisor, $nombreComprobante)
{
  /* global $folioInt, $total, $formaPDF, $metodoPDF, $descuentoTotal,  $subtotal, $ivaTotal, $idCliente, $tipoFactura, $db, $dataProductos,
    $receptorNombre,
    $receptorRFC,
    $receptorUsoCFDIPDF,
    $dataEmisor,
    $fecha,
    $objetoImpuestoPDF; */

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

      /**
       * @var EmisoresHelper $emisor
       */
      global $emisor;

      $this->SetFont('Arial', 'B', 8);
      $this->Cell(20);
      // Mostrar el logo en la cabecera
      // Imagen original: 408x74 px. Ajustamos el ancho a 40 mm y la altura proporcional.
      $this->Image(INVOICE_LOGO, 10, 5, INVOICE_LOGO_WIDTH, INVOICE_LOGO_HEIGHT, 'PNG');
      //$this->Image(INVOICE_LOGO, 10, 5, INVOICE_LOGO_WIDTH, INVOICE_LOGO_HEIGHT, 'PNG');
      $this->Ln(30);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($emisor->getBusinessName()), 0, 0, '');
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($emisor->getRfc()), 0, 0);
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode("RÉGIMEN FISCAL: " . $emisor->getFiscalRegimeText() . ""), 0, 0);
      $this->Ln(4);
      $this->SetFont('Arial', '', 8);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode("Expedido en: {$emisor->getAddress()}"), 0, 0);
      $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode("Lugar de Expedición: {$emisor->getPostalCode()}"), 0, 0);
      /* $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['Ciudad'] . ' CP ' . $dataEmisor['CP']), 0, 0); */
    }
    function Footer()
    {
      $this->SetY(-5);
      $this->SetFont('Arial', 'I', 8);
      //$this->Cell(0, 0, utf8_decode("ventasweb@cocinaspaal.com | 961 121 3404 | 961 121 5704 | cocinaspaal.com"), 0, 0, 'R', true);
      $this->Cell(0, 0, utf8_decode(INVOICE_FOOTER), 0, 0, 'R', true);
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
  global $serie;
  global $folio;
  global $fechaXML;

  /**
   * @var EmisoresHelper $emisor
   */
  global $emisor;
  global $dataSucursal;

  $pdf->SetDrawColor(255, 255, 255);
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);

  $pdf->SetFont('Arial', 'B', 12);

  $pdf->Cell(60, 5, utf8_decode("TRASLADO"), 1, 0, "L", false);
  $pdf->Ln(5);

  $pdf->SetXY(140, 10);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(60, 5, "COMPROBANTE FISCAL DIGITAL", 1, 0, "L", false);
  $pdf->Ln(10);

  $pdf->SetFont('Arial', '', 8);

  $pdf->SetDrawColor(224, 224, 224);

  /**
   * START SERIE
   */
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "SERIE", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $serie, 1, 0, "L");
  $pdf->Ln(5);
  /**
   * END SERIE
   */

  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->SetFillColor(224, 224, 224);
  $pdf->Cell(60, 5, "FOLIO", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  $pdf->Cell(60, 5, $folio, 1, 0, "L");
  $pdf->Ln(5);
  //fin del codigo para agregar el FOLIO interno

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
  $pdf->Cell(60, 5, $emisor->getCertificateNumber(), 1, 0, "L");
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
  $pdf->Cell(60, 5, "FECHA DE EXPEDICION", 1, 0, "L", true);
  $pdf->Ln(5);
  $pdf->SetFont('Arial', '', 7);
  $pdf->SetX(140);
  $pdf->SetTextColor(0, 0, 0);
  //$pdf->Cell(60, 5,utf8_decode($emisor['Municipio']).", ".utf8_decode($emisor['Estado']).", ".date('Y-m-d'),1,0,"L");
  $pdf->MultiCell(60, 5, $fechaXML, 1, "L");
  $pdf->Ln(30);
  $pdf->SetY(60);
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

  $receptorInfo = "";
  $receptorInfo .= "Nombre: {$emisor->getBusinessName()}\n";
  $receptorInfo .= "RFC: {$emisor->getRfc()}\n";
  $receptorInfo .= "Regimen Fiscal: {$emisor->getFiscalRegimeText()}\n";
  $receptorInfo .= "Domicilio Fiscal: {$emisor->getAddress()}\n";
  // $receptorInfo .= "Expedido en: {$emisor->getAddress()}\n";
  // $receptorInfo .= "Lugar de Expedicion: {$emisor->getAddress()}";

  $pdf->MultiCell(110, 5, utf8_decode($receptorInfo), 0, "L");

  //$pdf->MultiCell(110, 5, utf8_decode($receptorNombre) . "\n" . utf8_decode($receptorRFC) . "\n" . utf8_decode('Uso del CFDI: ' . $receptorUsoCFDIPDF), 0, "L");
  //$pdf->SetXY(0, 65);
  $pdf->SetY(85);
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

  global $conceptos;

  foreach ($conceptos as $concepto) :
    $objetoImpuesto   = "01";
    $claveProdServ    = $concepto['clave_producto_servicio'];
    $noIdentificacion = $concepto['codigo'];
    $cantidad         = $concepto['cantidad'];
    $claveUnidad      = $concepto['clave_unidad'];
    $unidad           = /* $concepto['unidad'] */ "PZ";
    $descripcion      = $concepto['nombre_producto'];
    $valorUnitario    = "0";
    $importe          = "0";
    $tipoConcepto     = $concepto['tipo'];

    $serialNumbers        = $concepto['serial_numbers'] ?? [];
    $serialNumbersLength  = count($serialNumbers);

    if ($serialNumbersLength > 0) $descripcion .= "\nS/N: " . implode(" ", $serialNumbers);

    $pdf->Row(array(
      utf8_decode($cantidad),
      utf8_decode($claveUnidad),
      utf8_decode($claveProdServ),
      utf8_decode($descripcion),
      utf8_decode($valorUnitario),
      //utf8_decode($descuento),
      utf8_decode($importe),
    ));
  endforeach;

  $pdf->Cell(140, 5, "Cantidad con letra:  " . numtoletras("0"), 0, 0, "L");

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "Subtotal:", 1, 0, "R");
  $pdf->Cell(25, 5, number_format("0", 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);

  $pdf->Cell(140, 5, "Forma de Pago: " . utf8_decode("{}"), 0, 0, "L");

  /* $pdf->SetX(145);
        $pdf->Cell(30,5,utf8_decode("ISH 2.00%"),1,0,"R");
        $pdf->Cell(25,5,number_format($ishTotal,2,".",","),1,0,"R");
        $pdf->Ln(5); */

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "IVA 16.00%", 1, 0, "R");
  $pdf->Cell(25, 5, number_format("0", 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);


  $pdf->Cell(140, 5, utf8_decode("Método de Pago: " . "{}"), 0, 0, "L");

  $pdf->SetX(145);
  $pdf->Cell(30, 5, "TOTAL:", 1, 0, "R");
  $pdf->Cell(25, 5, number_format("0", 2, ".", ","), 1, 0, "R");
  $pdf->Ln(5);


  //$pdf->Cell(140, 5, utf8_decode("Objeto de impuesto: " . $objetoImpuestoPDF), 0, 0, "L");


  $pdf->SetY(215);
  $pdf->Cell(0, 0, "Este documento es una representacion impresa de un CFDI", 0, 0, "L");
  $pdf->Ln(11);

  $pdf->Image(FCS_CARPETA_PATH . $UUID . '.png', 10, null, 30, 30, 'PNG');

  $pdf->SetFont('Arial', '', 5);
  $pdf->SetXY(43, 220);
  $pdf->MultiCell(55, 5, utf8_decode("Sello Timbre") . "\n||" . $version . "|" . $UUID . "|" . $fechaTimbrado . "|" . $CFD . "|" . $certificadoSAT, 1, 'L');

  $pdf->SetXY(100, 220);
  $pdf->MultiCell(53, 5, "Sello del SAT\n" . $SAT, 1, "L");

  $pdf->SetXY(155, 220);
  $pdf->MultiCell(45, 5, "Sello Digital\n" . $CFD, 1, "L");

  $archivo_PDF = FCS_CARPETA_PATH . $UUID . ".pdf";
  $pdf->Output($archivo_PDF, 'F');

  $nombreArchivoPDF = $UUID . ".pdf";

  /**
   * Guardar la factura en la DB
   */
  global $mysqli;

  global $idUsoCfdi, $inventoryTransferId, $moneda, $serie, $folio, $inventoryTransferData;


  $id_emisor                   = $emisor->getId();
  $id_receptor                 = $emisor->getId();
  $id_uso_cfdi                 = $idUsoCfdi;
  $subtotal                    = 0;
  $exportacion                 = "02";
  $total                       = 0;
  $comentarios                 = "Sin comentarios";
  $enviado                     = 0;
  $cancelado                   = 0;
  $pagado                      = 0;
  $fecha                       = date("Y-m-d");
  $tipo_relacion               = "";
  $cfdi_relacionado            = "";
  $id_sucursal                 = $inventoryTransferData['id_sucursal_destino'];

  $invoiceModel = new FacturasTrasladoModel();

  $invoiceModel->setIssuerId($id_emisor);
  $invoiceModel->setReceiverId($id_receptor);
  $invoiceModel->setUseCfdiId($id_uso_cfdi);
  $invoiceModel->setInventoryTransferId($inventoryTransferId);
  $invoiceModel->setSerie($serie);
  $invoiceModel->setFolio($folio);
  $invoiceModel->setUuid($UUID);
  $invoiceModel->setCurrency($moneda);
  $invoiceModel->setSubtotal($subtotal);
  $invoiceModel->setExport($exportacion);
  $invoiceModel->setTotal($total);
  $invoiceModel->setComments($comentarios);
  $invoiceModel->setSent($enviado);
  $invoiceModel->setCancelled($cancelado);
  $invoiceModel->setPaid($pagado);
  $invoiceModel->setDate($fecha);
  $invoiceModel->setRelationType($tipo_relacion);
  $invoiceModel->setCfdiRelated($cfdi_relacionado);
  $invoiceModel->setBranchId($id_sucursal);
  $invoiceModel->create();

  $query = "UPDATE paal_inventario_transferencias SET facturado = 1 WHERE id_inventario_transferencia = {$inventoryTransferId}";
  mysqli_query($mysqli, $query);

  /* if (isset($_POST['enviar_al_correo']) and  $_POST['enviar_al_correo'] == 'si' and  trim($_POST['correo']) != '') :
    $sender       = new stdClass();
    $sender->name = $emisor->getBusinessName();

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

    if ($customer->email) :
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
  endif; */

  //insertamos el nuevo folio consumido
  //$db->Exec("INSERT INTO facturacion_folios(Folio,Fecha,idUserCaptura) VALUES ('".$folioInt."',NOW(),'".$_SESSION["Med_idUsuario"]."')");


  return $archivo_PDF;
}

TimbrarFactura();
