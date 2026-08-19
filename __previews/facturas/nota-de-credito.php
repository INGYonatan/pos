<?php
const FCS_CARPETA_URL   = CARPETA_FACTURAS_NOTA_DE_CREDITO_URL;
const FCS_CARPETA_PATH  = CARPETA_FACTURAS_NOTA_DE_CREDITO_PATH;

$result     = mysqli_query($mysqli, "SELECT MAX(folio) as Num FROM paal_facturas_nota_credito WHERE serie = '{$folioSerie}'");
$dataFlolio = mysqli_fetch_assoc($result);
$folioInt   = $dataFlolio['Num'] + 1;

function GenerarPDF()
{
  global $folioInt, $total, $formaPDF, $metodoPDF, $descuentoTotal,  $subtotal, $ivaTotal, $idCliente, $tipoFactura, $db, $dataProductos,
    $receptorNombre,
    $receptorRFC,
    $receptorUsoCFDIPDF,
    $dataEmisor,
    $fecha,
    $objetoImpuestoPDF;


  $UUID = "EXAMPLE-UUID-1234-5678-9012-ABCDEFGHIJ";


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
  $receptorInfo .= "Domicilio Fiscal: {$data['domicilio_fiscal']}";

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
  $iepsTotal = $usePostedIepsTotal ? doubleval($iepsTotalFromPost) : 0;

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

  $pdf->Image(__DIR__ .  '/qr-example.png', 10, null, 30, 30, 'PNG');
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

  $archivo_PDF = $UUID . ".pdf";
  $pdf->Output($archivo_PDF, 'I');

  $nombreArchivoPDF = $UUID . ".pdf";

  return $archivo_PDF;
}

GenerarPDF();
