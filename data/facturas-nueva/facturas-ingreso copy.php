<?php

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

  $archivo_cer        = BASE_PATH . "/src/assets/facturacion/utilerias/certificados/" . $dataEmisor['ArchivoCer'];
  $archivo_pem        = BASE_PATH . "/src/assets/facturacion/utilerias/certificados/" . $dataEmisor['ArchivoKey'];

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
  $url_timbrado = "https://wsdemo.dinvbox.mx/timbrado/wsdl";
  $user_id = "UsuarioPruebasWS";
  $user_password = "b9ec2afa3361a59af4b4d102d3f704eabdf097d4";

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
    $comprobante = BASE_PATH . '/src/assets/facturacion/comprobantes/' . $cliente->UUID;
    $nombreComprobante = $cliente->UUID . '.xml';
    $ubicacionComprobante = BASE_URL . "/src/assets/facturacion/comprobantes/" . $cliente->UUID . ".pdf";

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
  global $dataEmisor, $receptorNombre, $receptorRFC, $receptorUsoCFDI, $metodoPago, $formaPago, $subtotal, $descuentoTotal, $ivaTotal, $total, $folioInt, $dataProductos, $db, $domicilioFiscalReceptor, $regimenFiscalReceptor, $fechaFacturacion, $objetoImpuesto;
  //$fecha_actual = substr( date('c'), 0, 19);
  $fecha_actual = $fechaFacturacion;

  global $tipoFactura;

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
  $xml->writeAttribute('TipoDeComprobante', $tipoFactura); //en la versión anterior era la palabra completa "Ingreso"
  $xml->writeAttribute('LugarExpedicion', $dataEmisor["PostalCode"]);
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
  $cfdiRelacionado  = $_POST["cfdi_relacionado"];

  if ($tipoRelacion != "" && $cfdiRelacionado != "") :
    $xml->startElementNs('cfdi', 'CfdiRelacionados', null);

    $xml->writeAttribute('TipoRelacion', $tipoRelacion);

    $xml->startElementNs('cfdi', 'CfdiRelacionado', null);
    $xml->writeAttribute('UUID', $cfdiRelacionado);
    $xml->endElement();

    $xml->endElement();
  endif;
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
      $iva            = $producto['ivaCurrency'];

      $objetoImp      = $producto['taxObject'];
      //$objetoImp      = $arrayObjetoImpuesto[$objetoImpuesto];

      $xml->startElementNs('cfdi', 'Concepto', null);
      $xml->writeAttribute('ClaveProdServ', $claveProdServ);
      $xml->writeAttribute('Cantidad', $cantidad);
      $xml->writeAttribute('ClaveUnidad', $claveUnidad);
      $xml->writeAttribute('Descripcion', $descripcion);
      $xml->writeAttribute('ValorUnitario', $valorUnitario);
      $xml->writeAttribute('Descuento', $descuento);
      $xml->writeAttribute('Importe', $importe);
      $xml->writeAttribute('ObjetoImp', $objetoImp);

      if ($iva > 0) :
        $xml->startElementNs('cfdi', 'Impuestos', null);
        $xml->startElementNs('cfdi', 'Traslados', null);
        $xml->startElementNs('cfdi', 'Traslado', null);
        $xml->writeAttribute('Base', $importe);
        $xml->writeAttribute('Impuesto', '002');
        $xml->writeAttribute('TipoFactor', 'Tasa');
        $xml->writeAttribute('TasaOCuota', '0.160000');
        $xml->writeAttribute('Importe', $iva);
        $xml->endElement();
        $xml->endElement();
        $xml->endElement();
      endif;

      $xml->endElement();
    endforeach;
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
      $this->Image(INVOICE_LOGO, 10, 5, INVOICE_LOGO_WIDTH, INVOICE_LOGO_HEIGHT, 'PNG');
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
      $this->Cell(0, 0, utf8_decode($dataEmisor['direccion']), 0, 0);
      /* $this->Ln(4);
      $this->Cell(20);
      $this->SetX(10);
      $this->Cell(0, 0, utf8_decode($dataEmisor['Ciudad'] . ' CP ' . $dataEmisor['CP']), 0, 0); */
    }
    function Footer()
    {
      $this->SetY(-5);
      $this->SetFont('Arial', 'I', 8);
      $this->Cell(0, 0, utf8_decode("ventasweb@cocinaspaal.com | 961 121 3404 | 961 121 5704 | cocinaspaal.com"), 0, 0, 'R', true);
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
  $pdf->SetDrawColor(255, 255, 255);
  $pdf->SetFillColor(255, 255, 255);
  $pdf->SetTextColor(0, 0, 0);

  $pdf->SetFont('Arial', 'B', 12);

  if ($tipoFactura == "E") :
    $pdf->Cell(60, 5, utf8_decode("NOTA DE CRÉDITO"), 1, 0, "L", false);
    $pdf->Ln(5);
  else :
    $pdf->Cell(60, 5, utf8_decode("FACTURA"), 1, 0, "L", false);
    $pdf->Ln(5);
  endif;

  $pdf->SetXY(140, 10);
  $pdf->SetFont('Arial', 'B', 8);
  $pdf->Cell(60, 5, "COMPROBANTE FISCAL DIGITAL", 1, 0, "L", false);
  $pdf->Ln(10);

  $pdf->SetFont('Arial', '', 8);

  $pdf->SetDrawColor(224, 224, 224);

  $pdf->SetX(140);
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
  $pdf->Cell(60, 5, utf8_decode('Tuxtla guiterrez') . ", " . utf8_decode('Chiapas') . ", " . $fecha, 1, 0, "L");
  $pdf->Ln(30);
  $pdf->SetY(70);
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

  $receptorInfo = "";
  $receptorInfo .= "Nombre: {$data['nombre_completo']}\n";
  $receptorInfo .= "RFC: {$data['rfc']}\n";
  $receptorInfo .= "Regimen Fiscal: {$data['regimen_fiscal']}\n";
  $receptorInfo .= "Domicilio Fiscal: {$data['domicilio_fiscal']}\n";
  $receptorInfo .= "Expedido en: {$data['domicilio_fiscal']}\n";
  $receptorInfo .= "Lugar de Expedicion: {$data['domicilio_fiscal']}";

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
      $claveProdServ  = $facturacionHelpers->getkeyProductoServicio($producto['keyProductServiceId']);
      $cantidad       = $producto['quantity'];
      $claveUnidad    = $facturacionHelpers->getkeyUnidad($producto['unitMeasurementId']);
      $descripcion    = $producto['productName'];
      $valorUnitario  = $producto['priceWithoutIVA'];
      $descuento      = $producto["discount"];
      $importe        = $producto['amountWithoutIVA'];
      $iva            = $producto['ivaCurrency'];

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


  //$pdf->Cell(140, 5, utf8_decode("Objeto de impuesto: " . $objetoImpuestoPDF), 0, 0, "L");


  $pdf->SetY(215);
  $pdf->Cell(0, 0, "Este documento es una representacion impresa de un CFDI", 0, 0, "L");
  $pdf->Ln(11);

  $pdf->Image(BASE_PATH . '/src/assets/facturacion/comprobantes/' . $UUID . '.png', 10, null, 30, 30, 'PNG');

  $pdf->SetFont('Arial', '', 5);
  $pdf->SetXY(43, 220);
  $pdf->MultiCell(55, 5, utf8_decode("Sello Timbre") . "\n||" . $version . "|" . $UUID . "|" . $fechaTimbrado . "|" . $CFD . "|" . $certificadoSAT, 1, 'L');

  $pdf->SetXY(100, 220);
  $pdf->MultiCell(53, 5, "Sello del SAT\n" . $SAT, 1, "L");

  $pdf->SetXY(155, 220);
  $pdf->MultiCell(45, 5, "Sello Digital\n" . $CFD, 1, "L");

  $archivo_PDF = BASE_PATH . '/src/assets/facturacion/comprobantes/' . $UUID . ".pdf";
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
  $serie          = "FRX";
  $folio          = $folioInt;
  $uuid           = $UUID;
  $metodo_pago    = ($_POST['metodo_pago'] == '') ? 'PUE' : $_POST['metodo_pago'];
  $moneda         = "MXN";
  $totalf         = $_POST["total"];
  $comentarios    = "Sin comentarios";
  $fecha          = date('Y-m-d', strtotime($_POST['fecha_emision']));
  $tipoFactura    = $_POST['tipo_factura'];

  /**
   * Insertar la factura
   */
  $query = "INSERT INTO paal_facturas (
      id_emisor,
      id_cliente,
      id_uso_cfdi,
      id_forma_pago,
      id_venta,
      serie,
      folio,
      uuid,
      metodo_pago,
      moneda,
      total,
      comentarios,
      fecha
    ) VALUES (
      {$id_emisor},
      {$id_cliente},
      {$id_uso_cfdi},
      {$id_forma_pago},
      {$id_venta},
      '{$serie}',
      {$folio},
      '{$uuid}',
      '{$metodo_pago}',
      '{$moneda}',
      '{$totalf}',
      '{$comentarios}',
      '{$fecha}'
    )
  ";

  $query_result = mysqli_query($mysqli, $query);
  $id_factura   = mysqli_insert_id($mysqli);

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

        if ($sendResponse["status"] == "success") mysqli_query($mysqli, "UPDATE paal_facturas SET enviado = 1 WHERE id_factura = {$id_factura}");
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
