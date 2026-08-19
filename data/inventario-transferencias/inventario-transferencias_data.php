<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../lib/settings.inc.php';
require_once __DIR__ . "/../lib/php-mailer/vendor/autoload.php";

$IS_ADMIN = $admp_session_user_data["IS_ADMIN"];

$response = [
  'status'        => 'error',
  'toastMessage'  => '¡Error inesperado!, intentalo nuevamente'
];

$action     = $_POST['action'];
$identifier = 'inventario-transferencias';
$id_sucursal = getSessionBranchOfficeId();

switch ($action) {
  case 'load-' . $identifier:
    $have_actions     = haveActions($identifier, 'tabla');

    $per_page         = $_POST['perPage'];
    $page             = $_POST['page'];

    $search               = cleanStr($_POST['search']);
    $fecha_inicio         = cleanStr($_POST['fecha_inicio']);
    $fecha_fin            = cleanStr($_POST['fecha_fin']);
    $id_sucursal_origen   = $IS_ADMIN ? cleanStr($_POST['id_sucursal_origen']) : getSessionBranchOfficeId();
    $id_sucursal_destino  = cleanStr($_POST['id_sucursal_destino']);

    $column_id        = "IT.id_inventario_transferencia";
    $c_from           = "{$db_dti}_inventario_transferencias AS IT";
    $c_extra_clauses  = "ORDER BY IT.id_inventario_transferencia DESC";

    $fields = [
      "IT.id_inventario_transferencia",
      ["IT.id_inventario_transferencia", "uid"],
      "IT.id_usuario",
      "IT.id_sucursal_origen",
      "IT.id_sucursal_destino",
      "IT.observaciones",
      "IT.status",
      "IT.tipo",
      "IT.fecha_creacion",
      "IT.folio",
      "IT.facturado",
      ["DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y %h:%i %p')", "fecha_creacion_formato"],
      ["OS.nombre_sucursal", "nombre_sucursal_origen"],
      ["DS.nombre_sucursal", "nombre_sucursal_destino"],
      "U.nombre_completo"
    ];

    $c_join = "
        LEFT JOIN {$db_dti}_sucursales  AS OS ON (IT.id_sucursal_origen   = OS.id_sucursal)
        LEFT JOIN {$db_dti}_sucursales  AS DS ON (IT.id_sucursal_destino  = DS.id_sucursal)
        LEFT JOIN {$db_ati}_usuarios    AS U  ON (IT.id_usuario           = U.id_usuario)
      ";

    $c_where = [
      /* ["IT.id_sucursal_origen", $id_sucursal] */];

    if (!empty($search))              array_push($c_where, ["IT.observaciones",                           "%$search%", "LIKE"]);
    if (!empty($id_sucursal_origen))  array_push($c_where, ["IT.id_sucursal_origen",                      $id_sucursal_origen]);
    if (!empty($id_sucursal_destino)) array_push($c_where, ["IT.id_sucursal_destino",                     $id_sucursal_destino]);

    if ($fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", [$fecha_inicio, $fecha_fin], "BETWEEN"]);
    if ($fecha_inicio && !$fecha_fin) array_push($c_where, ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", $fecha_inicio]);
    if (!$fecha_inicio && $fecha_fin) array_push($c_where, ["(DATE_FORMAT(IT.fecha_creacion, '%d-%m-%Y'))", $fecha_fin]);

    $request = useDataTable([
      'column_id'     => $column_id,
      'from'          => $c_from,
      'where'         => $c_where,
      'fields'        => $fields,
      'join'          => $c_join,
      'extra_clauses' => $c_extra_clauses,
      'per_page'      => $per_page,
      'page'          => $page
    ]);

    //echo getEmptyTableMessage($request);
    //die;

    if ($request['status'] === 'error')   echo getEmptyTableMessage();
    if ($request['status'] === 'success') include $identifier . '_table.php';
    die;
    break;

  case 'action-cancelar-' . $identifier:
    if (checkModuleActionPermission($identifier, 'cancelar')) :
      $id_inventario_transferencia = cleanStr($_POST['uid']);

      $query = "SELECT
          ITP.id_inventario_transferencia_producto,
          ITP.id_inventario_transferencia,
          ITP.id_producto,
          ITP.cantidad,
          ITP.cancelado,
          IT.id_sucursal_origen,
          IT.id_sucursal_destino,
          IO.stock AS stock_origen,
          ID.stock AS stock_destino,
          P.nombre_producto
        FROM
          {$db_dti}_inventario_transferencia_productos AS ITP
        LEFT JOIN
          {$db_dti}_inventario_transferencias AS IT ON (ITP.id_inventario_transferencia = IT.id_inventario_transferencia)
        LEFT JOIN
          {$db_dti}_inventario AS IO ON (
            IT.id_sucursal_origen = IO.id_sucursal AND
            ITP.id_producto       = IO.id_producto
          )
        LEFT JOIN
          {$db_dti}_inventario AS ID ON (
            IT.id_sucursal_destino  = ID.id_sucursal AND
            ITP.id_producto         = ID.id_producto
          )
        LEFT JOIN
          {$db_dti}_productos AS P ON (ITP.id_producto = P.id_producto)
        WHERE
          ITP.id_inventario_transferencia = ?
      ";

      $stmt = $mysqli->prepare($query);

      $stmt->bind_param('i', $id_inventario_transferencia);
      $stmt->execute();

      $query_result = $stmt->get_result();
      $num_rows     = $query_result->num_rows;

      if ($num_rows > 0) :
        while ($row = mysqli_fetch_assoc($query_result)) :
          $data_sucursal_origen   = getBranchOfficeData($row['id_sucursal_origen']);
          $data_sucursal_destino  = getBranchOfficeData($row['id_sucursal_destino']);

          $data_producto = [
            'id_producto'     => $row['id_producto'],
            'nombre_producto' => $row['nombre_producto'],
            'cantidad'        => doubleval($row['cantidad']),
            'stock_origen'    => doubleval($row['stock_origen']),
            'stock_destino'   => doubleval($row['stock_destino'])
          ];

          addLogInKardex(
            $row['id_sucursal_origen'],
            $data_producto,
            ACCION_CANCELAR_TRANSFERENCIA . ' que se realizó hacia ' . $data_sucursal_destino['nombre_sucursal'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'transferencia-almacen'
          );

          addLogInKardex(
            $row['id_sucursal_destino'],
            $data_producto,
            ACCION_CANCELAR_TRANSFERENCIA . ' que realizó ' . $data_sucursal_origen['nombre_sucursal'],
            TIPO_MOVIMIENTO_DECREMENTO,
            'transferencia-sucursal'
          );

        //$query = "UPDATE {$db_dti}_paal_producto_numeros_serie";
        endwhile;

        $query = "UPDATE {$db_dti}_inventario_transferencias SET
            status = 'cancelado'
          WHERE
            id_inventario_transferencia = {$id_inventario_transferencia}
        ";

        $query_result = mysqli_query($mysqli, $query);

        if ($query_result) $response = [
          'status'        => 'success',
          'toastMessage'  => 'La transferencia se canceló correctamente',
          'callback'      => 'load("' . $page . '", "' . $identifier . '");'
        ];
      endif;
    endif;
    break;

  case 'action-imprimir-ticket-' . $identifier:
    if (checkModuleActionPermission($identifier, 'imprimir-ticket')) :
      $uid        = $_POST['uid'];
      $url_ticket = BASE_URL . '/ticket-transferencia.php?uid=' . $uid;

      $response = [
        'status'    => 'success',
        'callback'  => "window.open('{$url_ticket}', '_blank')"
      ];
    endif;
    break;

  case "action-generar-factura-{$identifier}-old":
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

    # Obtener los datos de la transferencia
    $invoiceLabelType       = "traslado";
    $inventoryTransferId    = $_POST["uid"];
    $inventoryTransferData  = getInventoryTransferData($inventoryTransferId);

    if (!$inventoryTransferData) :
      $response["toastMessage"] = "¡No se encontró la transferencia!";
      break;
    endif;

    # SUCURSAL
    $dataSucursal = getBranchOfficeData($inventoryTransferData['id_sucursal']);

    # SERIE FOLIO
    $folioSerie = $dataSucursal["numero_serie"] . INVOICE_SERIES_ABREVIATIONS[$tipoFacturaLabel];
    $serialFolio      = INVOICE_SERIES_ABREVIATIONS[$invoiceLabelType];

    # Productos
    $productos = $inventoryTransferData['productos'];

    # Obtener datos del emisor
    $query        = "SELECT id_emisor FROM {$db_dti}_emisores LIMIT 1";
    $queryResult  = mysqli_query($mysqli, $query);
    $data         = mysqli_fetch_assoc($queryResult);

    $id_emisor    = $data['id_emisor'];
    $emisor       = new EmisoresHelper();
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

    # RECEPTOR S01
    $receptorNombre           = $emisor->getBusinessName();
    $receptorRFC              = $emisor->getRfc();
    $domicilioFiscalReceptor  = $emisor->getAddress();
    $regimenFiscalReceptor    = $emisor->getFiscalRegimeId();
    $receptorUsoCFDIKey       = "S01";

    // Obtener el uso de CFDI
    $query            = "SELECT id FROM uso_cfdi WHERE uso_cfdi = '{$receptorUsoCFDIKey}' LIMIT 1";
    $queryResult      = mysqli_query($mysqli, $query);
    $row              = mysqli_fetch_assoc($queryResult);
    $receptorUsoCFDI  = $row['id'];

    $receptorUsoCFDI          = $receptorUsoCFDIKey;
    $receptorUsoCFDIPDF       = $receptorUsoCFDIKey;
    $idCliente                = $emisor->getId();

    // detalles del pago
    $metodoPago               = isset($_POST['metodo_pago'])    ? $_POST['metodo_pago']   : 'PUE';
    $formaPago                = $_POST['id_forma_pago'];
    break;

  case "action-generar-factura-{$identifier}":
    require_once __DIR__ . "/../lib/facturacion/facturacion-moderna/facturacion-moderna.php";
    //require __DIR__ . "/../lib/facturacion/fpdf17/fpdf.php";
    require_once __DIR__ . "/../lib/facturacion/fpdf-1.86/fpdf.php";
    require_once __DIR__ . "/../lib/facturacion/arrays.php";
    require_once __DIR__ . "/../lib/facturacion/helpers.php";

    require_once __DIR__ . "/../lib/helpers/emisores.helpers.php";

    /**
     * Modelos de facturas
     */
    require_once __DIR__ . "/../lib/models/facturas-traslado.model.php";
    require_once __DIR__ . "/../lib/models/facturas-anticipo-compra.model.php";
    require_once __DIR__ . "/../lib/models/facturas-notas-credito.model.php";

    # Obtener los datos de la transferencia
    $inventoryTransferId    = $_POST["uid"];
    $inventoryTransferData  = getInventoryTransferData($inventoryTransferId);

    if (!$inventoryTransferData) :
      $response["toastMessage"] = "¡No se encontró la transferencia!";
      break;
    endif;

    $dataSucursal = getBranchOfficeData($inventoryTransferData['id_sucursal_origen']);

    # Información del emisor
    # Obtener datos del emisor
    $query        = "SELECT id_emisor FROM {$db_dti}_emisores LIMIT 1";
    $queryResult  = mysqli_query($mysqli, $query);
    $data         = mysqli_fetch_assoc($queryResult);

    $id_emisor    = $data['id_emisor'];
    $emisor       = new EmisoresHelper();
    $emisor->get($id_emisor);

    # Serie para el folio
    $tipoFacturaLabel         = "traslado";
    $serie                    = INVOICE_SERIES_ABREVIATIONS[$tipoFacturaLabel];

    // Folio int
    $result                   = mysqli_query($mysqli, "SELECT MAX(folio) as Num FROM paal_facturas_traslado WHERE serie = '{$serie}'");
    $dataFlolio               = mysqli_fetch_assoc($result);
    $folio                    = $dataFlolio["Num"] + 1;

    $fecha                    = date("Y-m-d");
    $fechaXML                 = date("Y-m-d\TH:i:s");

    $subtotal                 = 0;
    $moneda                   = "XXX";
    $total                    = 0;
    $tipoComprobante          = INVOICE_TYPES_ABREVIATIONS[$tipoFacturaLabel];
    $lugarExpedicion          = $emisor->getPostalCode();

    $emisorRFC                = $emisor->getRfc();
    $emisorNombre             = $emisor->getBusinessName();
    $emisorRegimenFiscal      = $emisor->getFiscalRegimeId();

    $receptorRFC              = $emisor->getRfc();
    $receptorNombre           = $emisor->getBusinessName();
    $domicilioFiscalReceptor  = $emisor->getPostalCode();

    # Obtener el regimen fiscal
    $regimenFiscalReceptor    = $emisor->getFiscalRegimeId();

    $query                    = "SELECT regimen_fiscal FROM regimen_fiscal WHERE id_regimen_fiscal = {$regimenFiscalReceptor}";
    $queryResult              = mysqli_query($mysqli, $query);
    $data                     = mysqli_fetch_assoc($queryResult);

    $regimenFiscalReceptorLabel = $data["regimen_fiscal"];
    $usoCFDI                    = "S01";

    // Obtner uso de cfdi id
    $query                    = "SELECT id FROM uso_cfdi WHERE uso_cfdi = '{$usoCFDI}' LIMIT 1";
    $queryResult              = mysqli_query($mysqli, $query);
    $data                     = mysqli_fetch_assoc($queryResult);
    $idUsoCfdi                = $data['id'];

    $conceptos                = $inventoryTransferData["productos"];

    require_once __DIR__ . "/facturas-traslado.php";
    break;
}

echo json_encode($response);
mysqli_close($mysqli);
die;

/* 
Folio
Fecha
SubTotal = 0
Moneda = XXX
Total = 0
TipoDeComprobante = T
LugarExpedicion = CP Emisor

EmisorRFC
EmisorNombre
EmisorRegimenFiscal

ReceptorRFC
ReceptorNombre
DomicilioFiscalReceptor
RegimenFiscalReceptor
UsoCFDI = S01

// Conceptos
ObjetoImp = 01
ClaveProdServ
NoIdentificacion
Cantidad
ClaveUnidad
Unidad = PZ
Descripcion
ValorUnitario = 0

// Concepto parte
ClaveProdServ
NoIdentificacion
Cantidad = 1
Unidad = PZ
Descripcion
ValorUnitario = 1
Importe = 1

*/